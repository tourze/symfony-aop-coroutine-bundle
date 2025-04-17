<?php

namespace Tourze\Symfony\AopCoroutineBundle\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\Symfony\RuntimeContextBundle\Service\ContextServiceInterface;

#[AutoconfigureTag('monolog.processor')]
class CoroutineProcessor implements ProcessorInterface
{
    public function __construct(private readonly ContextServiceInterface $contextService)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['context_id'] = $this->contextService->getId();
        return $record;
    }
}
