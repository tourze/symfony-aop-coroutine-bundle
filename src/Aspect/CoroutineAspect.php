<?php

namespace Tourze\Symfony\AopCoroutineBundle\Aspect;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\Symfony\Aop\Attribute\Aspect;
use Tourze\Symfony\Aop\Attribute\Before;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\Aop\Service\InstanceService;
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;
use Tourze\Symfony\RuntimeContextBundle\Service\ContextServiceInterface;

/**
 * 如果需要拦截，那我们直接替换instance对象
 */
#[Aspect]
class CoroutineAspect implements EventSubscriberInterface, ResetInterface
{
    /**
     * @var object[][]
     */
    private static array $services = [];

    public function __construct(
        private readonly ContextServiceInterface $contextService,
        private readonly InstanceService $instanceService,
    ) {
    }

    #[Before(serviceIds: [
        // 请求 & 路由相关，这些服务因为涉及到请求信息，所以一般都要上协程
        'request_stack',
        'session_listener',
        'router_listener',

        // 从上下文来看，这个应该需要协程化
        // 因为 \Symfony\Component\Routing\RequestContext::setParameters 这里返回的是 static，跟 ProxyManager 冲突了，所以不能马上用
        // 要等 \Symfony\Component\Routing\RequestContext::setBaseUrl 这种方法的返回值不是 static 之后才能用
        // 'router.request_context', // TODO 这个服务很应该改造的，但是目前暂时动不了

        'twig', // Twig服务内部也涉及到一些状态变化，这里需要协程化
        'twig.loader.native_filesystem',

        // 'security.token_storage',
        'security.untracked_token_storage',
        'security.csrf.token_storage',
        'debug.stopwatch',

        // 日志最好还是分开，默认有一堆日志服务，我们这里使用通配符来查找所有
        'monolog.logger',
        'monolog.logger.*',

        // 'profiler_listener', // 这个服务会在两个事件之间共享一个 Exception 对象，代理之后有一些异常问题
    ])]
    #[Before(serviceTags: ['as-coroutine'])]
    #[Before(parentClasses: [
        \SessionHandlerInterface::class,
        \SessionUpdateTimestampHandlerInterface::class,
        \Doctrine\ORM\EntityManagerInterface::class,
    ])]
    #[Before(classAttribute: AsCoroutine::class)]
    public function replaceInstance(JoinPoint $joinPoint): void
    {
        $serviceId = $joinPoint->getInternalServiceId();
        $contextId = $this->contextService->getId();

        if (!isset(static::$services[$contextId])) {
            static::$services[$contextId] = [];
        }
        if (!isset(static::$services[$contextId][$serviceId])) {
            static::$services[$contextId][$serviceId] = $this->instanceService->create($joinPoint);
            // var_dump("创建协程服务:{$contextId} -> {$serviceId}");
            // debug_print_backtrace();
        }

        $joinPoint->setInstance(static::$services[$contextId][$serviceId]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['reset', -10999],
        ];
    }

    public function reset(): void
    {
        $contextId = $this->contextService->getId();
        // var_dump("reset协程:{$contextId}");
        // debug_print_backtrace();
        unset(static::$services[$contextId]);
    }
}
