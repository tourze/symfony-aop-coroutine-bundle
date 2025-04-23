<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

class AsCoroutineTest extends TestCase
{
    public function testAsCoroutineAttributeIsAttribute(): void
    {
        $reflectionClass = new \ReflectionClass(AsCoroutine::class);

        // 确保 AsCoroutine 类是一个 Attribute
        $this->assertTrue($reflectionClass->isSubclassOf(\Attribute::class) || $reflectionClass->getAttributes(\Attribute::class));

        // 测试 AsCoroutine 是 AutoconfigureTag 的子类
        $this->assertTrue($reflectionClass->isSubclassOf(AutoconfigureTag::class));
    }

    public function testAsCoroutineCallsParentConstructorWithTagName(): void
    {
        // 使用反射创建一个模拟的 AsCoroutine 来验证其行为
        $reflectionClass = new \ReflectionClass(AsCoroutine::class);
        $constructor = $reflectionClass->getConstructor();

        // 验证构造函数是否存在
        $this->assertNotNull($constructor);

        // 设置构造函数为可访问
        $constructor->setAccessible(true);

        // 创建一个模拟的父类构造函数调用捕获器
        $parentConstructorCalled = false;
        $tagName = null;

        // 定义一个临时类来捕获父构造函数的调用
        $mockParent = new class($parentConstructorCalled, $tagName) extends AutoconfigureTag {
            private $parentConstructorCalled;
            private $tagName;

            public function __construct(&$parentConstructorCalled, &$tagName)
            {
                $this->parentConstructorCalled = &$parentConstructorCalled;
                $this->tagName = &$tagName;
            }

            public function __call($name, $arguments)
            {
                if ($name === '__construct') {
                    $this->parentConstructorCalled = true;
                    $this->tagName = $arguments[0];
                }
                return null;
            }
        };

        // 创建模拟 AsCoroutine 实例并手动调用其构造函数
        $asCoroutine = new AsCoroutine();

        // 直接访问 AsCoroutine 源代码验证
        $this->assertTrue(method_exists(AsCoroutine::class, '__construct'));

        // 最简单的测试：验证 AsCoroutine 没有重写 getName 方法
        $this->assertFalse(method_exists(AsCoroutine::class, 'getName'));

        // 使用反射来验证源代码中的行为
        $reflectionMethod = new \ReflectionMethod(AsCoroutine::class, '__construct');
        $lines = file($reflectionMethod->getFileName());
        $startLine = $reflectionMethod->getStartLine() - 1;
        $endLine = $reflectionMethod->getEndLine();
        $methodBody = implode('', array_slice($lines, $startLine, $endLine - $startLine));

        // 确认构造函数中传递了 'coroutine-service' 给父类构造函数
        $this->assertStringContainsString("parent::__construct('coroutine-service')", $methodBody);
    }

    public function testAttributeTarget(): void
    {
        $reflectionClass = new \ReflectionClass(AsCoroutine::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertNotEmpty($attributes, 'AsCoroutine 类应该标记为 Attribute');

        $attribute = $attributes[0]->newInstance();

        // 验证 Attribute 目标是否正确（只能用于类）
        $this->assertEquals(\Attribute::TARGET_CLASS, $attribute->flags);
    }
}
