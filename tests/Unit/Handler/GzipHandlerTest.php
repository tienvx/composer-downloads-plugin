<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use LastCall\DownloadsPlugin\Subpackage;

class GzipHandlerTest extends FileHandlerTest
{
    protected function getHandlerClass(): string
    {
        return GzipHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'gzip';
    }

    protected function getChecksum(): string
    {
        return 'bb11858b3513500b4c3d234a17a8ea5f6790444cb93c457259a861d1682aec60';
    }

    protected function assertDownload(): void
    {
        $this->composer->expects($this->once())->method('getDownloadManager')->willReturn($this->downloadManager);
        if ($this->isComposerV2) {
            $this->loop
                ->expects($this->exactly(2))
                ->method('wait')
                ->withConsecutive(
                    [[$this->downloadPromise]],
                    [[$this->installPromise]]
                );
            $this->composer->expects($this->exactly(2))->method('getLoop')->willReturn($this->loop);
        }
        $this->filesystem
            ->expects($this->once())
            ->method('ensureDirectoryExists')
            ->with($this->callback(function (string $dir): bool {
                $this->assertStringContainsString(\dirname($this->targetPath), $dir);
                $this->assertStringContainsString(FileHandler::TMP_PREFIX, $dir);
                $tmpDir = $dir;
                $tmpFile = $tmpDir.\DIRECTORY_SEPARATOR.'file';
                if ($this->isComposerV2) {
                    $this->downloadManager
                        ->expects($this->once())
                        ->method('download')
                        ->with($this->isInstanceOf(Subpackage::class), $tmpDir)
                        ->willReturn($this->downloadPromise);
                    $this->downloadManager
                        ->expects($this->once())
                        ->method('install')
                        ->with($this->isInstanceOf(Subpackage::class), $tmpDir)
                        ->willReturn($this->installPromise);
                } else {
                    $this->downloadManager
                        ->expects($this->once())
                        ->method('download')
                        ->with($this->isInstanceOf(Subpackage::class), $tmpDir);
                }
                $this->filesystem->expects($this->once())->method('rename')->with($tmpFile, $this->targetPath);
                $this->filesystem->expects($this->once())->method('remove')->with($tmpDir);

                return true;
            }));
    }
}
