<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Composer;
use Composer\Downloader\FileDownloader;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Subpackage;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\BaseHandlerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use React\Promise\PromiseInterface;

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
        return \dirname($this->getTargetFilePath()).\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
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
        return 'bcede60ff885547a620ea6ec29039160a7d8360234c2878aadf44e1e4b8ec0ec';
    }

    protected function assertDownload(): void
    {
        $this->composer->expects($this->once())->method('getDownloadManager')->willReturn($this->downloadManager);
        $isComposerV2 = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0;
        if ($isComposerV2) {
            $tmpFile = '/path/to/vendor/composer/tmp-random';
            $downloadPromise = $this->createMock(PromiseInterface::class);
            $downloadPromise
                ->expects($this->once())
                ->method('then')
                ->willReturnCallback(fn (callable $callback) => $callback($tmpFile));
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), \dirname($this->getTargetFilePath()))
                ->willReturn($downloadPromise);
            $loop = $this->createMock(Loop::class);
            $loop
                ->expects($this->once())
                ->method('wait')
                ->with([$downloadPromise]);
            $this->composer->expects($this->once())->method('getLoop')->willReturn($loop);
            $this->filesystem->expects($this->once())->method('rename')->with($tmpFile, $this->getTargetFilePath());
        } else {
            $tmpDir = null;
            $tmpFile = $tmpDir.\DIRECTORY_SEPARATOR.'/file';
            $this->filesystem
                ->expects($this->once())
                ->method('ensureDirectoryExists')
                ->with($this->callback(function (string $dir) use (&$tmpDir): bool {
                    $this->assertStringContainsString(\dirname($this->getTargetFilePath()), $dir);
                    $this->assertStringContainsString(FileHandler::TMP_PREFIX, $dir);
                    $tmpDir = $dir;

                    return true;
                }));
            $downloader = $this->createMock(FileDownloader::class);
            $downloader
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), $tmpDir)
                ->willReturn($tmpFile);
            $this->downloadManager
                ->expects($this->once())
                ->method('getDownloader')
                ->with('file')
                ->willReturn($downloader);
            $this->filesystem->expects($this->once())->method('rename')->with($tmpFile, $this->getTargetFilePath());
            $this->filesystem->expects($this->once())->method('remove')->with($tmpDir);
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

    protected function getTargetFilePath(): string
    {
        return $this->targetPath;
    }
}
