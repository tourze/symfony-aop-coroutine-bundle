<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Service;

use Tourze\Symfony\Aop\Service\InstanceService;
use Tourze\Symfony\AopCoroutineBundle\Aspect\CoroutineAspectEventSubscriber;
use Tourze\Symfony\RuntimeContextBundle\Service\ContextServiceInterface;

/**
 * 工厂类用于在测试中创建 CoroutineAspect 实例
 */
final class CoroutineAspectFactory
{
    /**
     * 创建 CoroutineAspect 实例
     */
    public static function create(
        ContextServiceInterface $contextService,
        InstanceService $instanceService,
    ): CoroutineAspectEventSubscriber {
        return new CoroutineAspectEventSubscriber($contextService, $instanceService);
    }
}
