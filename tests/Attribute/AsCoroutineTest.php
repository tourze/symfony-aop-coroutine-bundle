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
        $attributes = $reflectionClass->getAttributes(\Attribute::class);
        $this->assertNotEmpty($attributes, 'AsCoroutine should have Attribute annotation');

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

        // 创建 AsCoroutine 实例
        $asCoroutine = new AsCoroutine();
        
        // 验证实例创建成功
        $this->assertInstanceOf(AsCoroutine::class, $asCoroutine);
        $this->assertInstanceOf(AutoconfigureTag::class, $asCoroutine);

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
