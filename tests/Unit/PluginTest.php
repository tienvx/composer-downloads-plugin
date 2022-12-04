<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use LastCall\DownloadsPlugin\PackageInstaller;
use LastCall\DownloadsPlugin\Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private PackageInstaller|MockObject $installer;
    private Plugin $plugin;
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;

    protected function setUp(): void
    {
        $this->installer = $this->createMock(PackageInstaller::class);
        $this->plugin = new Plugin($this->installer);
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            PackageEvents::POST_PACKAGE_INSTALL => ['installDownloads', 10],
            PackageEvents::POST_PACKAGE_UPDATE => ['updateDownloads', 10],
            ScriptEvents::POST_INSTALL_CMD => ['installDownloadsRoot', 10],
            ScriptEvents::POST_UPDATE_CMD => ['installDownloadsRoot', 10],
        ], Plugin::getSubscribedEvents());
    }

    public function testActivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->activate($this->composer, $this->io);
    }

    public function testDeactivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->deactivate($this->composer, $this->io);
    }

    public function testUninstall(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->uninstall($this->composer, $this->io);
    }

    public function testInstallDownloadsRoot(): void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $this->composer->expects($this->once())->method('getPackage')->willReturn($rootPackage);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $this->composer->expects($this->once())->method('getRepositoryManager')->willReturn($repositoryManager);
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager->expects($this->once())->method('getLocalRepository')->willReturn($localRepository);
        $packages = [
            $this->createMock(PackageInterface::class),
            $this->createMock(PackageInterface::class),
            $this->createMock(PackageInterface::class),
        ];
        $localRepository->expects($this->once())->method('getCanonicalPackages')->willReturn($packages);
        $this->installer
            ->expects($this->exactly(\count($packages) + 1))
            ->method('install')
            ->withConsecutive(
                [$rootPackage, $this->composer, $this->io],
                ...array_map(fn (PackageInterface $package) => [$package, $this->composer, $this->io], $packages),
            );
        $event = new Event('name', $this->composer, $this->io);
        $this->plugin->installDownloadsRoot($event);
    }

    public function testInstallDownloads(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $this->installer
            ->expects($this->once())
            ->method('install')
            ->with($package, $this->composer, $this->io);
        if (version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0) {
            $event = new PackageEvent(
                'name',
                $this->composer,
                $this->io,
                false,
                $this->createMock(RepositoryInterface::class),
                [],
                new InstallOperation($package)
            );
        } else {
            $event = new PackageEvent(
                'name',
                $this->composer,
                $this->io,
                false,
                $this->createMock(PolicyInterface::class),
                $this->createMock(Pool::class),
                $this->createMock(CompositeRepository::class),
                $this->createMock(Request::class),
                [],
                new InstallOperation($package)
            );
        }
        $this->plugin->installDownloads($event);
    }

    public function testUpdateDownloads(): void
    {
        $initial = $this->createMock(PackageInterface::class);
        $target = $this->createMock(PackageInterface::class);
        $this->installer
            ->expects($this->once())
            ->method('install')
            ->with($target, $this->composer, $this->io);
        if (version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0) {
            $event = new PackageEvent(
                'name',
                $this->composer,
                $this->io,
                false,
                $this->createMock(RepositoryInterface::class),
                [],
                new UpdateOperation($initial, $target)
            );
        } else {
            $event = new PackageEvent(
                'name',
                $this->composer,
                $this->io,
                false,
                $this->createMock(PolicyInterface::class),
                $this->createMock(Pool::class),
                $this->createMock(CompositeRepository::class),
                $this->createMock(Request::class),
                [],
                new UpdateOperation($initial, $target)
            );
        }
        $this->plugin->updateDownloads($event);
    }
}
