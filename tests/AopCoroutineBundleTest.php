<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle;

class AopCoroutineBundleTest extends TestCase
{
    public function testBundleCreation(): void
    {
        // 简单测试 bundle 实例化
        $bundle = new AopCoroutineBundle();
        $this->assertInstanceOf(AopCoroutineBundle::class, $bundle);
    }
}
