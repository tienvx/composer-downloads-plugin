<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;

class PackageInstaller
{
    private DownloadsParser $parser;
    private SubpackageInstaller $subInstaller;

    public function __construct(?DownloadsParser $parser = null, ?SubpackageInstaller $subInstaller = null)
    {
        $this->parser = $parser ?? new DownloadsParser();
        $this->subInstaller = $subInstaller ?? new SubpackageInstaller();
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
        foreach ($this->parser->parse($package, $basePath) as $handler) {
            /** @var HandlerInterface $handler */
            if ($this->subInstaller->isInstalled($handler, $io)) {
                continue;
            }

            if ($first) {
                $io->write(sprintf('<info>Download extra files for <comment>%s</comment></info>', $package->getName()));
                $first = false;
            }

            $this->subInstaller->install($handler, $composer, $io);
        }
    }
}
