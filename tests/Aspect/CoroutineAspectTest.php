<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Aspect;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\Symfony\AopCoroutineBundle\Aspect\CoroutineAspect;
use Tourze\Symfony\AopCoroutineBundle\Tests\TestHelper;

class CoroutineAspectTest extends TestCase
{
    use TestHelper;

    protected function setUp(): void
    {
        // No need for setup in this test
    }

    public function testGetSubscribedEvents(): void
    {
        $events = CoroutineAspect::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $events);
        $this->assertEquals(['reset', -10999], $events[KernelEvents::TERMINATE]);
    }

    public function testReplaceInstance(): void
    {
        // 重新创建带有更具体配置的 mock 对象
        $contextId = 'test-context-id';
        $serviceId = 'test-service-id';
        $testInstance = new \stdClass();

        $contextService = $this->createContextServiceMock($contextId);
        $instanceService = $this->createInstanceServiceMock($testInstance);

        $aspect = new CoroutineAspect($contextService, $instanceService);

        // 创建 JoinPoint mock
        $joinPoint = $this->createJoinPointMock($serviceId);

        $joinPoint->expects($this->once())
            ->method('setInstance')
            ->with($testInstance);

        // 执行测试方法
        $aspect->replaceInstance($joinPoint);
    }

    public function testReset(): void
    {
        $contextId = 'test-context-id';
        $contextService = $this->createContextServiceMock($contextId);
        $instanceService = $this->createInstanceServiceMock();

        $aspect = new CoroutineAspect($contextService, $instanceService);

        // 使用反射来设置私有静态属性的初始值
        $reflectionClass = new \ReflectionClass(CoroutineAspect::class);
        $servicesProperty = $reflectionClass->getProperty('services');
        $servicesProperty->setAccessible(true);

        $initialValue = [
            $contextId => ['some-service' => new \stdClass()],
            'other-context' => ['some-service' => new \stdClass()]
        ];
        $servicesProperty->setValue(null, $initialValue);

        // 调用 reset 方法
        $aspect->reset();

        // 检查对应的上下文是否被移除
        $services = $servicesProperty->getValue();
        $this->assertArrayNotHasKey($contextId, $services);
        $this->assertArrayHasKey('other-context', $services);
    }
}
