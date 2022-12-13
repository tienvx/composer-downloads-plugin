<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\GlobCleaner;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ArchiveHandlerTestCase extends BaseHandlerTestCase
{
    private GlobCleaner|MockObject $cleaner;

    protected function setUp(): void
    {
        $this->cleaner = $this->createMock(GlobCleaner::class);
        parent::setUp();
        $this->extraFile += [
            'ignore' => $this->ignore,
        ];
    }

    protected function getHandlerExtraArguments(): array
    {
        return [$this->cleaner];
    }

    protected function assertDownload(): void
    {
        $this->composer->expects($this->once())->method('getDownloadManager')->willReturn($this->downloadManager);
        if ($this->isComposerV2) {
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath)
                ->willReturn($this->downloadPromise);
            $this->downloadManager
                ->expects($this->once())
                ->method('install')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath)
                ->willReturn($this->installPromise);
            $this->loop
                ->expects($this->exactly(2))
                ->method('wait')
                ->withConsecutive(
                    [[$this->downloadPromise]],
                    [[$this->installPromise]]
                );
            $this->composer->expects($this->exactly(2))->method('getLoop')->willReturn($this->loop);
        } else {
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath);
        }
        $this->cleaner->expects($this->once())->method('clean')->with($this->targetPath, $this->ignore);
    }

    protected function getTrackingFile(): string
    {
        return $this->targetPath.\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }

    protected function getExecutableType(): string
    {
        return 'array';
    }

    protected function getTrackingData(): array
    {
        return [
            'ignore' => $this->ignore,
            'name' => "{$this->parentName}:{$this->id}",
            'url' => $this->url,
            'checksum' => $this->getChecksum(),
        ];
    }
}
