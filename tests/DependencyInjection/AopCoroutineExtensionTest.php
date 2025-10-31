<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\Symfony\AopCoroutineBundle\Aspect\CoroutineAspectEventSubscriber;
use Tourze\Symfony\AopCoroutineBundle\DependencyInjection\AopCoroutineExtension;
use Tourze\Symfony\AopCoroutineBundle\Logger\CoroutineProcessor;

/**
 * @internal
 */
#[CoversClass(AopCoroutineExtension::class)]
final class AopCoroutineExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.project_dir', __DIR__ . '/../../');

        $extension = new AopCoroutineExtension();
        $extension->load([], $container);

        // 测试 CoroutineAspectEventSubscriber 服务是否已注册
        $this->assertTrue($container->hasDefinition(CoroutineAspectEventSubscriber::class));

        // 测试 CoroutineProcessor 服务是否已注册
        $this->assertTrue($container->hasDefinition(CoroutineProcessor::class));
    }
}
