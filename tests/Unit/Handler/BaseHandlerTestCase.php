<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class BaseHandlerTestCase extends TestCase
{
    protected Composer|MockObject $composer;
    protected IOInterface|MockObject $io;
    protected DownloadManager|MockObject $downloadManager;
    protected BinariesInstaller|MockObject $binariesInstaller;
    protected PackageInterface|MockObject $parent;
    protected string $parentPath = '/path/to/package';
    protected string $id = 'sub-package-name';
    protected string $url = 'http://example.com/file.ext';
    protected string $path = 'files/file';
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
        $this->expectExceptionMessage(sprintf('Attribute "executable" of extra file "%s" defined in package "%s" must be '.$this->getExecutableType().', "%s" given.', $this->id, $this->parentName, $type));
        $handler->getSubpackage();
    }

    public function testGetTrackingData(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $this->assertSame($this->getTrackingData(), $handler->getTrackingData());
    }

    public function testGetChecksum(): void
    {
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
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
        $this->assertDownload();
        $this->assertBinariesInstaller();
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile);
        $handler->install($this->composer, $this->io);
    }

    protected function assertBinariesInstaller(): void
    {
        $this->binariesInstaller
            ->expects($this->once())
            ->method('install')
            ->with($this->isInstanceOf(Subpackage::class), $this->parentPath, $this->io);
    }

    abstract protected function assertDownload(): void;

    abstract protected function getHandlerClass(): string;

    abstract protected function getHandlerExtraArguments(): array;

    abstract protected function getTrackingFile(): string;

    abstract protected function getDistType(): string;

    abstract protected function getChecksum(): string;

    abstract protected function getExecutableType(): string;

    abstract protected function getTrackingData(): array;

    protected function createHandler(PackageInterface $parent, string $parentPath, array $extraFile): HandlerInterface
    {
        $class = $this->getHandlerClass();

        return new $class($parent, $parentPath, $extraFile, $this->binariesInstaller, ...$this->getHandlerExtraArguments());
    }
}
