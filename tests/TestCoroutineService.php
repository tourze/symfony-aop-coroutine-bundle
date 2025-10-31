<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests;

use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

/**
 * 测试用的协程服务类
 */
#[AsCoroutine]
final class TestCoroutineService
{
    private mixed $state = null;

    public function setState(mixed $value): void
    {
        $this->state = $value;
    }

    public function getState(): mixed
    {
        return $this->state;
    }
}
