<?php

namespace Tourze\Symfony\AopCoroutineBundle\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\Symfony\Aop\Service\ContextService;

#[AutoconfigureTag('monolog.processor')]
class CoroutineProcessor implements ProcessorInterface
{
    public function __construct(private readonly ContextService $contextService)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->contextService->isCoroutineRuntime()) {
            $record->extra['coroutine_id'] = $this->contextService->getId();
        }

        return $record;
    }
}
