<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * 这个用来标记指定服务是协程相关的，容器会自动根据上下文切换对应的服务。
 * 具体内部实现，就是使用对象克隆。
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS)]
class AsCoroutine extends AutoconfigureTag
{
    public function __construct()
    {
        parent::__construct('coroutine-service');
    }
}
