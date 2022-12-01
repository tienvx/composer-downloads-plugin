<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use Composer\Util\Loop;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\GlobCleaner;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;

abstract class BaseHandlerTestCase extends TestCase
{
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    private DownloadManager|MockObject $downloadManager;
    private GlobCleaner|MockObject $cleaner;
    private BinariesInstaller|MockObject $binariesInstaller;
    protected PackageInterface|MockObject $parent;
    protected string $parentPath = '/path/to/package';
    protected string $id = 'sub-package-name';
    private string $url = 'http://example.com/file.ext';
    private string $path = 'files/file';
    protected array $extraFile;
    protected string $targetPath;
    private array $ignore = ['file.*', '!file.ext'];
    protected string $parentName = 'vendor/parent-package';

    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->cleaner = $this->createMock(GlobCleaner::class);
        $this->binariesInstaller = $this->createMock(BinariesInstaller::class);
        $this->parent = $this->createMock(PackageInterface::class);
        $this->extraFile = [
            'id' => $this->id,
            'url' => $this->url,
            'path' => $this->path,
        ];
        $this->targetPath = $this->parentPath.\DIRECTORY_SEPARATOR.$this->path;
    }

    public function testGetSubpackageWithExplicitVersion(): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['version' => '1.2.3']);
        $version = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0 ? 'dev-master' : '9999999-dev';
        $expectSubpackage = new Subpackage($this->parent, $this->id, $this->url, $this->getDistType(), $this->path, $version, '1.2.3');
        $this->assertEquals($expectSubpackage, $handler->getSubpackage());
        $this->assertSame([], $handler->getSubpackage()->getBinaries());
    }

    public function testGetSubpackageFromRootPackage(): void
    {
        $rootPackage = $this->createMock(RootPackage::class);
        $handler = $this->createHandler($rootPackage, $this->parentPath, $this->extraFile);
        $version = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0 ? 'dev-master' : '9999999-dev';
        $expectSubpackage = new Subpackage($rootPackage, $this->id, $this->url, $this->getDistType(), $this->path, $version, 'dev-master');
        $this->assertEquals($expectSubpackage, $handler->getSubpackage());
        $this->assertSame([], $handler->getSubpackage()->getBinaries());
    }

    public function testGetSubpackageFromNormalPackage(): void
    {
        $this->parent->expects($this->once())->method('getVersion')->willReturn('1.2.3.0');
        $this->parent->expects($this->once())->method('getPrettyVersion')->willReturn('v1.2.3');
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $expectSubpackage = new Subpackage($this->parent, $this->id, $this->url, $this->getDistType(), $this->path, '1.2.3.0', 'v1.2.3');
        $this->assertEquals($expectSubpackage, $handler->getSubpackage());
        $this->assertSame([], $handler->getSubpackage()->getBinaries());
    }

    abstract public function getBinariesTests(): array;

    /**
     * @dataProvider getBinariesTests
     */
    public function testSubpackageBinaries(mixed $executable, array $expectedBinaries): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['executable' => $executable]);
        $this->assertSame($expectedBinaries, $handler->getSubpackage()->getBinaries());
    }

    abstract public function getInvalidBinariesTests(): array;

    /**
     * @dataProvider getInvalidBinariesTests
     */
    public function testSubpackageInvalidBinaries(mixed $executable, string $type): void
    {
        $this->parent->expects($this->exactly(2))->method('getName')->willReturn($this->parentName);
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['executable' => $executable]);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Attribute "executable" of extra file "%s" defined in package "%s" must be array, "%s" given.', $this->id, $this->parentName, $type));
        $handler->getSubpackage();
    }

    public function testGetTrackingData(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['ignore' => $this->ignore]);
        $this->assertSame([
            'ignore' => $this->ignore,
            'name' => "{$this->parentName}:{$this->id}",
            'url' => $this->url,
            'checksum' => $this->getChecksum(),
        ], $handler->getTrackingData());
    }

    public function testGetChecksum(): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['ignore' => $this->ignore]);
        $this->assertSame($this->getChecksum(), $handler->getChecksum());
    }

    public function testGetTargetPath(): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $this->assertSame($this->targetPath, $handler->getTargetPath());
    }

    public function testGetTrackingFile(): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $this->assertSame($this->getTrackingFile(), $handler->getTrackingFile());
    }

    public function testInstall(): void
    {
        $this->composer->expects($this->once())->method('getDownloadManager')->willReturn($this->downloadManager);
        $isComposerV2 = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0;
        if ($isComposerV2) {
            $downloadPromise = $this->createMock(PromiseInterface::class);
            $installPromise = $this->createMock(PromiseInterface::class);
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath)
                ->willReturn($downloadPromise);
            $this->downloadManager
                ->expects($this->once())
                ->method('install')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath)
                ->willReturn($installPromise);
            $loop = $this->createMock(Loop::class);
            $loop
                ->expects($this->exactly(2))
                ->method('wait')
                ->withConsecutive(
                    [[$downloadPromise]],
                    [[$installPromise]]
                );
            $this->composer->expects($this->exactly(2))->method('getLoop')->willReturn($loop);
        } else {
            $this->downloadManager
                ->expects($this->once())
                ->method('download')
                ->with($this->isInstanceOf(Subpackage::class), $this->targetPath);
        }
        $this->binariesInstaller
            ->expects($this->once())
            ->method('install')
            ->with($this->isInstanceOf(Subpackage::class), $this->parentPath, $this->io);
        $this->cleaner->expects($this->once())->method('clean')->with($this->targetPath, $this->ignore);
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['ignore' => $this->ignore]);
        $handler->install($this->composer, $this->io);
    }

    abstract protected function getHandlerClass(): string;

    abstract protected function getTrackingFile(): string;

    protected function getDistType(): string
    {
        return 'file';
    }

    abstract protected function getChecksum(): string;

    protected function createHandler(PackageInterface $parent, string $parentPath, array $extraFile): HandlerInterface
    {
        $class = $this->getHandlerClass();

        return new $class($parent, $parentPath, $extraFile, $this->binariesInstaller, $this->cleaner);
    }
}
