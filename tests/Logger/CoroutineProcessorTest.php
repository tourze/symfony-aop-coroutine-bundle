<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCoroutineBundle\Tests\Logger;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\Symfony\AopCoroutineBundle\Logger\CoroutineProcessor;

/**
 * @internal
 */
#[CoversClass(CoroutineProcessor::class)]
#[RunTestsInSeparateProcesses]
final class CoroutineProcessorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 可以在这里进行额外的设置
    }

    public function testProcessorAddsCoroutineIdToRecord(): void
    {
        self::bootKernel();

        // 从容器获取处理器实例
        /** @var CoroutineProcessor $processor */
        $processor = self::getContainer()->get(CoroutineProcessor::class);

        // 创建日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Info,
            'test message',
            [],
            []
        );

        // 处理日志记录
        $processedRecord = $processor($record);

        // 验证是否添加了协程ID（可能是实际的协程ID或默认值）
        $this->assertArrayHasKey('context_id', $processedRecord->extra);
        $this->assertIsString($processedRecord->extra['context_id']);
    }

    public function testProcessorPreservesExistingExtraData(): void
    {
        self::bootKernel();

        // 从容器获取处理器实例
        /** @var CoroutineProcessor $processor */
        $processor = self::getContainer()->get(CoroutineProcessor::class);

        // 创建带有额外数据的日志记录
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Info,
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
        $this->assertIsString($processedRecord->extra['context_id']);
    }
}
