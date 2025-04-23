<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

/**
 * 测试 AsCoroutine 属性在服务上的应用
 */
class AsCoroutineAttributeTest extends TestCase
{
    /**
     * 创建测试用的服务类
     */
    private function createTestServiceClass(): string
    {
        // 动态创建测试类
        $className = 'TestCoroutineService' . uniqid();

        $classCode = <<<EOT
namespace Tourze\Symfony\AopCoroutineBundle\Tests\Integration;

use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class {$className}
{
    private \$state;
    
    public function setState(\$value): void
    {
        \$this->state = \$value;
    }
    
    public function getState()
    {
        return \$this->state;
    }
}
EOT;

        eval($classCode);
        return "Tourze\\Symfony\\AopCoroutineBundle\\Tests\\Integration\\{$className}";
    }

    public function testAsCoroutineAttributeIsAppliedToClass(): void
    {
        $className = $this->createTestServiceClass();

        // 使用反射检查类上是否应用了 AsCoroutine 属性
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(AsCoroutine::class);

        $this->assertNotEmpty($attributes, 'AsCoroutine 属性应该应用到类上');
        $this->assertInstanceOf(AsCoroutine::class, $attributes[0]->newInstance());
    }

    public function testServiceStateIsolation(): void
    {
        $className = $this->createTestServiceClass();

        // 创建两个服务实例
        $service1 = new $className();
        $service2 = new $className();

        // 设置不同的状态
        $service1->setState('value1');
        $service2->setState('value2');

        // 验证状态隔离
        $this->assertEquals('value1', $service1->getState());
        $this->assertEquals('value2', $service2->getState());
    }
}
