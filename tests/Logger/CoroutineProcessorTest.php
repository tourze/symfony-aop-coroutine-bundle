<?php

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Logger;

use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCoroutineBundle\Logger\CoroutineProcessor;
use Tourze\Symfony\AopCoroutineBundle\Tests\TestHelper;

class CoroutineProcessorTest extends TestCase
{
    use TestHelper;

    public function testProcessorAddsCoroutineIdToRecord(): void
    {
        // 创建 ContextService 的模拟
        $contextService = $this->createContextServiceMock('test-coroutine-id');

        // 创建处理器实例
        $processor = new CoroutineProcessor($contextService);

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            \Monolog\Level::Info,
            'test message',
            [],
            []
        );

        // 处理日志记录
        $processedRecord = $processor($record);

        // 验证是否添加了协程ID
        $this->assertArrayHasKey('context_id', $processedRecord->extra);
        $this->assertEquals('test-coroutine-id', $processedRecord->extra['context_id']);
    }

    public function testProcessorPreservesExistingExtraData(): void
    {
        // 创建 ContextService 的模拟
        $contextService = $this->createContextServiceMock('test-coroutine-id');

        // 创建处理器实例
        $processor = new CoroutineProcessor($contextService);

        // 创建带有额外数据的日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            \Monolog\Level::Info,
            'test message',
            [],
            ['existing_key' => 'existing_value']
        );

        // 处理日志记录
        $processedRecord = $processor($record);

        // 验证是否保留了现有数据并添加了协程ID
        $this->assertArrayHasKey('existing_key', $processedRecord->extra);
        $this->assertEquals('existing_value', $processedRecord->extra['existing_key']);
        $this->assertArrayHasKey('context_id', $processedRecord->extra);
        $this->assertEquals('test-coroutine-id', $processedRecord->extra['context_id']);
    }
}
