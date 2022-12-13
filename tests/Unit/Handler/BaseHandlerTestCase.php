<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Util\Loop;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;

abstract class BaseHandlerTestCase extends TestCase
{
    protected Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    protected DownloadManager|MockObject $downloadManager;
    private BinariesInstaller|MockObject $binariesInstaller;
    protected PromiseInterface|MockObject $downloadPromise;
    protected PromiseInterface|MockObject $installPromise;
    protected Loop|MockObject $loop;
    private Subpackage $subpackage;
    private HandlerInterface $handler;
    protected bool $isComposerV2;
    private string $parentPath = '/path/to/package';
    protected string $id = 'sub-package-name';
    protected string $url = 'http://example.com/file.ext';
    protected string $path = 'files/new-file';
    protected array $extraFile;
    protected string $targetPath;
    protected array $ignore = ['file.*', '!file.ext'];
    protected string $parentName = 'vendor/parent-package';

    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->binariesInstaller = $this->createMock(BinariesInstaller::class);
        $this->isComposerV2 = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0;
        if ($this->isComposerV2) {
            $this->downloadPromise = $this->createMock(PromiseInterface::class);
            $this->installPromise = $this->createMock(PromiseInterface::class);
            $this->loop = $this->createMock(Loop::class);
        }
        $this->extraFile = [
            'id' => $this->id,
            'url' => $this->url,
            'path' => $this->path,
        ];
        $this->targetPath = $this->parentPath.\DIRECTORY_SEPARATOR.$this->path;
        $this->subpackage = new Subpackage(
            new Package($this->parentName, '1.0.0', 'v1.0.0'),
            $this->parentPath,
            $this->id,
            $this->getSubpackageType(),
            ['file1', 'dir/file2'],
            $this->ignore,
            $this->url,
            $this->path,
            '1.2.3.0',
            'v1.2.3'
        );
        $this->handler = $this->createHandler();
    }

    public function testGetSubpackage(): void
    {
        $this->assertSame($this->subpackage, $this->handler->getSubpackage());
    }

    public function testGetTrackingData(): void
    {
        $this->assertSame($this->getTrackingData(), $this->handler->getTrackingData());
    }

    public function testGetChecksum(): void
    {
        $this->assertSame($this->getChecksum(), $this->handler->getChecksum());
    }

    public function testGetTrackingFile(): void
    {
        $this->assertSame($this->getTrackingFile(), $this->handler->getTrackingFile());
    }

    public function testInstall(): void
    {
        $this->assertDownload();
        $this->assertBinariesInstaller();
        $this->handler->install($this->composer, $this->io);
    }

    protected function assertBinariesInstaller(): void
    {
        $this->binariesInstaller
            ->expects($this->once())
            ->method('install')
            ->with($this->isInstanceOf(Subpackage::class), $this->io);
    }

    abstract protected function assertDownload(): void;

    abstract protected function getHandlerClass(): string;

    abstract protected function getHandlerExtraArguments(): array;

    abstract protected function getTrackingFile(): string;

    abstract protected function getSubpackageType(): string;

    abstract protected function getChecksum(): string;

    abstract protected function getExecutableType(): string;

    abstract protected function getTrackingData(): array;

    protected function createHandler(): HandlerInterface
    {
        $class = $this->getHandlerClass();

        return new $class($this->subpackage, $this->binariesInstaller, ...$this->getHandlerExtraArguments());
    }
}
