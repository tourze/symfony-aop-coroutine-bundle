<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\Aop\Service\InstanceService;
use Tourze\Symfony\RuntimeContextBundle\Service\ContextServiceInterface;

/**
 * 测试辅助 trait，提供常用的 mock 对象创建方法
 */
trait TestHelper
{
    /**
     * 创建一个 ContextService 的 mock
     */
    protected function createContextServiceMock(?string $contextId = 'test-context-id'): MockObject|ContextServiceInterface
    {
        $contextService = $this->createMock(ContextServiceInterface::class);
        if ($contextId !== null) {
            $contextService->method('getId')
                ->willReturn($contextId);
        }

        return $contextService;
    }

    /**
     * 创建一个 InstanceService 的 mock
     */
    protected function createInstanceServiceMock(?object $returnInstance = null): MockObject|InstanceService
    {
        $instanceService = $this->createMock(InstanceService::class);
        if ($returnInstance !== null) {
            $instanceService->method('create')
                ->willReturn($returnInstance);
        }

        return $instanceService;
    }

    /**
     * 创建一个 JoinPoint 的 mock
     */
    protected function createJoinPointMock(
        ?string $serviceId = 'test-service-id',
        ?object $instance = null
    ): MockObject|JoinPoint
    {
        $joinPoint = $this->createMock(JoinPoint::class);

        if ($serviceId !== null) {
            $joinPoint->method('getInternalServiceId')
                ->willReturn($serviceId);
        }

        if ($instance !== null) {
            $joinPoint->method('getInstance')
                ->willReturn($instance);
        }

        return $joinPoint;
    }
}
