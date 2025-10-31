<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\Symfony\RuntimeContextBundle\RuntimeContextBundle;

final class AopCoroutineBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            RuntimeContextBundle::class => ['all' => true],
        ];
    }
}
