<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Util\ProcessExecutor;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use PHPUnit\Framework\MockObject\MockObject;

class GzipHandlerTest extends FileHandlerTest
{
    protected ProcessExecutor|MockObject $process;

    protected function setUp(): void
    {
        parent::setUp();
        $this->process = $this->createMock(ProcessExecutor::class);
    }

    protected function getHandlerExtraArguments(): array
    {
        return [$this->filesystem, $this->process];
    }

    protected function getHandlerClass(): string
    {
        return GzipHandler::class;
    }

    protected function getChecksum(): string
    {
        return '512bfc7e6ab3c4b2279e18ecb4a33a98ed3a5a0b98e67cc68973767081442f74';
    }

    protected function assertDownload(bool $hasException = false): void
    {
        parent::assertDownload();
        $command = \PHP_OS_FAMILY === 'Windows'
            ? "gzip -df {$this->getTargetFilePath()}"
            : "gzip -df '{$this->getTargetFilePath()}'";
        $this->process
            ->expects($this->once())
            ->method('execute')
            ->with($command)
            ->willReturn((int) $hasException);
        if ($hasException) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("Failed to execute $command\n\nSomething wrong!");
            $this->process
                ->expects($this->once())
                ->method('getErrorOutput')
                ->willReturn('Something wrong!');
        } else {
            $this->process->expects($this->never())->method('getErrorOutput');
        }
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testInstall(bool $hasException = false): void
    {
        $this->assertDownload($hasException);
        if ($hasException) {
            $this->binariesInstaller->expects($this->never())->method('install');
        } else {
            $this->assertBinariesInstaller();
        }
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $handler->install($this->composer, $this->io);
    }

    protected function getTargetFilePath(): string
    {
        return $this->targetPath.'.gz';
    }
}
