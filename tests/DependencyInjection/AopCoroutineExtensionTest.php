<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\Symfony\AopCoroutineBundle\Aspect\CoroutineAspect;
use Tourze\Symfony\AopCoroutineBundle\DependencyInjection\AopCoroutineExtension;
use Tourze\Symfony\AopCoroutineBundle\Logger\CoroutineProcessor;

class AopCoroutineExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new AopCoroutineExtension();

        $extension->load([], $container);

        // 测试 CoroutineAspect 服务是否已注册
        $this->assertTrue($container->hasDefinition(CoroutineAspect::class));

        // 测试 CoroutineProcessor 服务是否已注册
        $this->assertTrue($container->hasDefinition(CoroutineProcessor::class));
    }
}
