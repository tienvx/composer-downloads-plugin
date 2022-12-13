<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;

class PackageInstaller
{
    public function __construct(
        private ?SubpackageFactory $factory = null,
        private ?SubpackageInstaller $subpackageInstaller = null
    ) {
    }

    public function install(PackageInterface $package, Composer $composer, IOInterface $io): void
    {
        if (empty($package->getExtra()['downloads'])) {
            return;
        }

        $installationManager = $composer->getInstallationManager();
        $basePath = $package instanceof RootPackageInterface ? getcwd() : $installationManager->getInstallPath($package);
        $this->downloadExtraFiles($basePath, $package, $composer, $io);
        if (!$package instanceof RootPackageInterface) {
            $installationManager->ensureBinariesPresence($package);
        }
    }

    private function downloadExtraFiles(string $basePath, PackageInterface $package, Composer $composer, IOInterface $io): void
    {
        $first = true;
        $factory = $this->factory ?? new SubpackageFactory();
        foreach ($factory->create($package, $basePath) as $subpackage) {
            $subpackageInstaller = $this->subpackageInstaller ?? new SubpackageInstaller($subpackage);
            if ($subpackageInstaller->isInstalled($io)) {
                continue;
            }

            if ($first) {
                $io->write(sprintf('<info>Download extra files for <comment>%s</comment></info>', $package->getName()));
                $first = false;
            }

            $subpackageInstaller->install($composer, $io);
        }
    }
}
