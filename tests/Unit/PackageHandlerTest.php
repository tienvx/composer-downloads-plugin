<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use LastCall\DownloadsPlugin\DownloadsParser;
use LastCall\DownloadsPlugin\Handler\BaseHandler;
use LastCall\DownloadsPlugin\PackageHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PackageHandlerTest extends TestCase
{
    private DownloadsParser|MockObject $parser;
    private PackageHandler $handler;
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    private PackageInterface|MockObject $package;
    private InstallationManager|MockObject $installationManager;
    private array $extra = ['downloads' => ['file1', 'file2', 'file3']];
    private array $handlers;

    protected function setUp(): void
    {
        $this->parser = $this->createMock(DownloadsParser::class);
        $this->handler = new PackageHandler($this->parser);
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->package = $this->createMock(PackageInterface::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        $this->handlers = [
            $this->createMock(BaseHandler::class),
            $this->createMock(BaseHandler::class),
            $this->createMock(BaseHandler::class),
        ];
    }

    /**
     * @testWith [[]]
     *           [{"key": "value"}]
     *           [{"downloads": []}]
     */
    public function testHandlePackageWithoutExtraFiles(array $extra): void
    {
        $this->composer->expects($this->never())->method('getInstallationManager');
        $this->package->expects($this->once())->method('getExtra')->willReturn($extra);
        $this->handler->handle($this->package, $this->composer, $this->io);
    }

    public function testHandleRootPackage(): void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $rootPackage->expects($this->once())->method('getExtra')->willReturn($this->extra);
        $rootPackage->expects($this->once())->method('getName')->willReturn('root/package-name');
        $this->composer->expects($this->once())->method('getInstallationManager')->willReturn($this->installationManager);
        $this->installationManager->expects($this->never())->method('getInstallPath');
        $this->installationManager->expects($this->never())->method('ensureBinariesPresence');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($rootPackage, getcwd())
            ->willReturnCallback(fn () => yield from $this->handlers);
        $this->handlers[0]->expects($this->once())->method('isInstalled')->willReturn(true);
        $this->handlers[0]->expects($this->never())->method('install');
        $this->handlers[1]->expects($this->once())->method('isInstalled')->willReturn(false);
        $this->handlers[1]->expects($this->once())->method('install')->with($this->composer, $this->io);
        $this->handlers[2]->expects($this->once())->method('isInstalled')->willReturn(false);
        $this->handlers[2]->expects($this->once())->method('install')->with($this->composer, $this->io);
        $this->io->expects($this->once())->method('write')->with('<info>Download extra files for <comment>root/package-name</comment></info>');
        $this->handler->handle($rootPackage, $this->composer, $this->io);
    }

    public function testHandleNormalPackage(): void
    {
        $basePath = '/path/to/install/path';
        $this->package->expects($this->once())->method('getExtra')->willReturn($this->extra);
        $this->package->expects($this->once())->method('getName')->willReturn('normal/package-name');
        $this->composer->expects($this->once())->method('getInstallationManager')->willReturn($this->installationManager);
        $this->installationManager->expects($this->once())->method('getInstallPath')->with($this->package)->willReturn($basePath);
        $this->installationManager->expects($this->once())->method('ensureBinariesPresence')->with($this->package);
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($this->package, $basePath)
            ->willReturnCallback(fn () => yield from $this->handlers);
        $this->handlers[0]->expects($this->once())->method('isInstalled')->willReturn(true);
        $this->handlers[0]->expects($this->never())->method('install');
        $this->handlers[1]->expects($this->once())->method('isInstalled')->willReturn(false);
        $this->handlers[1]->expects($this->once())->method('install')->with($this->composer, $this->io);
        $this->handlers[2]->expects($this->once())->method('isInstalled')->willReturn(false);
        $this->handlers[2]->expects($this->once())->method('install')->with($this->composer, $this->io);
        $this->io->expects($this->once())->method('write')->with('<info>Download extra files for <comment>normal/package-name</comment></info>');
        $this->handler->handle($this->package, $this->composer, $this->io);
    }
}
