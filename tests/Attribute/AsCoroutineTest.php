<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

/**
 * @internal
 */
#[CoversClass(AsCoroutine::class)]
final class AsCoroutineTest extends TestCase
{
    public function testAsCoroutineIsAttributeAndExtendsAutoconfigureTag(): void
    {
        $reflectionClass = new \ReflectionClass(AsCoroutine::class);

        // 确保 AsCoroutine 类是一个 Attribute
        $attributes = $reflectionClass->getAttributes(\Attribute::class);
        $this->assertNotEmpty($attributes, 'AsCoroutine should have Attribute annotation');

        // 测试 AsCoroutine 是 AutoconfigureTag 的子类
        $this->assertTrue($reflectionClass->isSubclassOf(AutoconfigureTag::class));
    }

    public function testAsCoroutineConstructorPassesTagName(): void
    {
        // 实际创建实例来验证行为
        $asCoroutine = new AsCoroutine();

        // 通过反射检查父类的 tags 属性值
        $parentClass = new \ReflectionClass(get_parent_class(AutoconfigureTag::class));
        $tagsProperty = $parentClass->getProperty('tags');

        // 验证tags数组结构符合预期
        $expectedTags = [['coroutine-service' => []]];
        $this->assertEquals($expectedTags, $tagsProperty->getValue($asCoroutine));
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
