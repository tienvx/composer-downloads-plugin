<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use LastCall\DownloadsPlugin\Exception\UnexpectedValueException;

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

        $this->downloadExtraFiles($package, $composer, $io);
        if (!$package instanceof RootPackageInterface) {
            $composer->getInstallationManager()->ensureBinariesPresence($package);
        }
    }

    private function downloadExtraFiles(PackageInterface $package, Composer $composer, IOInterface $io): void
    {
        $first = true;
        foreach ($this->getSubpackages($package, $composer, $io) as $subpackage) {
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

    private function getSubpackages(PackageInterface $package, Composer $composer, IOInterface $io): array
    {
        $basePath = $package instanceof RootPackageInterface ? getcwd() : $composer->getInstallationManager()->getInstallPath($package);

        try {
            $factory = $this->factory ?? new SubpackageFactory();

            return $factory->create($package, $basePath);
        } catch (UnexpectedValueException $exception) {
            $io->writeError(sprintf('    Skipped download extra files for package %s: %s', $package->getName(), $exception->getMessage()));

            return [];
        }
    }
}
