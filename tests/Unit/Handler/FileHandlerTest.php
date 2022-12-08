<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Downloader\FileDownloader;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Subpackage;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\BaseHandlerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FileHandlerTest extends BaseHandlerTestCase
{
    protected Filesystem|MockObject $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    protected function getHandlerExtraArguments(): array
    {
        return [$this->filesystem];
    }

    public function getBinariesTests(): array
    {
        return [
            [null, []],
            [true, [$this->path]],
            [false, []],
        ];
    }

    public function getInvalidBinariesTests(): array
    {
        return [
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    protected function getTrackingFile(): string
    {
        return \dirname($this->targetPath).\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }

    protected function getHandlerClass(): string
    {
        return FileHandler::class;
    }

    protected function getDistType(): string
    {
        return 'file';
    }

    protected function getChecksum(): string
    {
        return 'eb2ddda74e9049129d10e5b75520a374820a3826ca86a99f72a300cfe57320cc';
    }

    protected function assertDownload(): void
    {
        $this->composer->expects($this->once())->method('getDownloadManager')->willReturn($this->downloadManager);
        if ($this->isComposerV2) {
            $tmpFile = '/path/to/vendor/composer/tmp-random';
            $this->downloadPromise
                ->expects($this->once())
                ->method('then')
                ->willReturnCallback(fn (callable $callback) => $callback($tmpFile));
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), \dirname($this->targetPath))
                ->willReturn($this->downloadPromise);
            $this->loop
                ->expects($this->once())
                ->method('wait')
                ->with([$this->downloadPromise]);
            $this->composer->expects($this->once())->method('getLoop')->willReturn($this->loop);
            $this->filesystem->expects($this->once())->method('rename')->with($tmpFile, $this->targetPath);
        } else {
            $downloader = $this->createMock(FileDownloader::class);
            $this->filesystem
                ->expects($this->once())
                ->method('ensureDirectoryExists')
                ->with($this->callback(function (string $dir) use ($downloader): bool {
                    $this->assertStringContainsString(\dirname($this->targetPath), $dir);
                    $this->assertStringContainsString(FileHandler::TMP_PREFIX, $dir);
                    $tmpDir = $dir;
                    $tmpFile = $tmpDir.\DIRECTORY_SEPARATOR.'file';
                    $downloader
                        ->expects($this->once())
                        ->method('download')
                        ->with($this->isInstanceOf(Subpackage::class), $tmpDir)
                        ->willReturn($tmpFile);
                    $this->filesystem->expects($this->once())->method('rename')->with($tmpFile, $this->targetPath);
                    $this->filesystem->expects($this->once())->method('remove')->with($tmpDir);

                    return true;
                }));
            $this->downloadManager
                ->expects($this->once())
                ->method('getDownloader')
                ->with('file')
                ->willReturn($downloader);
        }
    }

    protected function getExecutableType(): string
    {
        return 'boolean';
    }

    protected function getTrackingData(): array
    {
        return [
            'name' => "{$this->parentName}:{$this->id}",
            'url' => $this->url,
            'checksum' => $this->getChecksum(),
        ];
    }
}
