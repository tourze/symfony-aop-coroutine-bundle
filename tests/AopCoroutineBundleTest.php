<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle;

/**
 * @internal
 */
#[CoversClass(AopCoroutineBundle::class)]
#[RunTestsInSeparateProcesses]
final class AopCoroutineBundleTest extends AbstractBundleTestCase
{
}
