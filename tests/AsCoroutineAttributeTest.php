<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

/**
 * 测试 AsCoroutine 属性在服务上的应用
 *
 * @internal
 */
#[CoversClass(AsCoroutine::class)]
final class AsCoroutineAttributeTest extends TestCase
{
    public function testAsCoroutineAttributeIsAppliedToClass(): void
    {
        // 使用反射检查类上是否应用了 AsCoroutine 属性
        $reflectionClass = new \ReflectionClass(TestCoroutineService::class);
        $attributes = $reflectionClass->getAttributes(AsCoroutine::class);

        $this->assertNotEmpty($attributes, 'AsCoroutine 属性应该应用到类上');
        $this->assertInstanceOf(AsCoroutine::class, $attributes[0]->newInstance());
    }

    public function testServiceStateIsolation(): void
    {
        // 创建两个服务实例
        $service1 = new TestCoroutineService();
        $service2 = new TestCoroutineService();

        // 设置不同的状态
        $service1->setState('value1');
        $service2->setState('value2');

        // 验证状态隔离
        $this->assertEquals('value1', $service1->getState());
        $this->assertEquals('value2', $service2->getState());
    }
}
