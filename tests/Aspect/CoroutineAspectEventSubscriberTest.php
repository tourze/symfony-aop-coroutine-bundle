<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Aspect;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\Aop\Service\InstanceService;
use Tourze\Symfony\AopCoroutineBundle\Aspect\CoroutineAspectEventSubscriber;
use Tourze\Symfony\AopCoroutineBundle\Tests\Service\CoroutineAspectFactory;
use Tourze\Symfony\RuntimeContextBundle\Service\ContextServiceInterface;

/**
 * @internal
 */
#[CoversClass(CoroutineAspectEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class CoroutineAspectEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    private ContextServiceInterface $contextService;

    private InstanceService $instanceService;

    private CoroutineAspectEventSubscriber $aspect;

    protected function onSetUp(): void
    {
        /*
         * createMock() 使用具体类 Tourze\Symfony\Aop\Service\InstanceService 时必须添加详细注释说明
         * 原因：InstanceService 是一个服务类，没有定义对应的接口
         * 合理性：我们需要模拟 create() 方法来测试协程管理的行为，这是合理的，因为我们测试的是协程与 InstanceService 的交互
         * 替代方案：可以为 InstanceService 创建一个接口，但这会增加不必要的复杂性
         */
        $this->instanceService = $this->createMock(InstanceService::class);
        $this->contextService = $this->createMock(ContextServiceInterface::class);

        // 使用工厂类创建 CoroutineAspect 实例，避免直接实例化
        $this->aspect = CoroutineAspectFactory::create($this->contextService, $this->instanceService);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = CoroutineAspectEventSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $events);
        $this->assertEquals(['reset', -10999], $events[KernelEvents::TERMINATE]);
    }

    public function testImplementsEventSubscriberInterface(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->aspect
        );
    }

    public function testReplaceInstance(): void
    {
        $contextId = 'test-context-id';
        $serviceId = 'test-service-id';
        $testInstance = new \stdClass();

        $this->contextService->method('getId')
            ->willReturn($contextId)
        ;

        $this->instanceService->method('create')
            ->willReturn($testInstance)
        ;

        /*
         * createMock() 使用具体类 Tourze\Symfony\Aop\Model\JoinPoint 时必须添加详细注释说明
         * 原因：JoinPoint 是 AOP 框架提供的具体类，没有定义对应的接口
         * 合理性：我们只需要一个占位符对象传递给协程处理器，这是合理的，因为 JoinPoint 是 AOP 框架的核心概念，其设计决定不在我们的控制范围内
         * 替代方案：创建 JoinPoint 接口需要修改整个 AOP 框架，成本过高且不现实
         */
        $joinPoint = $this->createMock(JoinPoint::class);
        $joinPoint->method('getInternalServiceId')
            ->willReturn($serviceId)
        ;

        $joinPoint->expects($this->once())
            ->method('setInstance')
            ->with($testInstance)
        ;

        // 执行测试方法
        $this->aspect->replaceInstance($joinPoint);
    }

    public function testReset(): void
    {
        $contextId = 'test-context-id';
        $this->contextService->method('getId')
            ->willReturn($contextId)
        ;

        // 使用反射来设置私有静态属性的初始值
        $reflectionClass = new \ReflectionClass(CoroutineAspectEventSubscriber::class);
        $servicesProperty = $reflectionClass->getProperty('services');
        $servicesProperty->setAccessible(true);

        $initialValue = [
            $contextId => ['some-service' => new \stdClass()],
            'other-context' => ['some-service' => new \stdClass()],
        ];
        $servicesProperty->setValue(null, $initialValue);

        // 调用 reset 方法
        $this->aspect->reset();

        // 检查对应的上下文是否被移除
        $services = $servicesProperty->getValue();
        $this->assertArrayNotHasKey($contextId, $services);
        $this->assertArrayHasKey('other-context', $services);
    }
}
