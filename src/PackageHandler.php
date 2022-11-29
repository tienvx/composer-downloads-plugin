<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use LastCall\DownloadsPlugin\Handler\BaseHandler;

class PackageHandler
{
    private DownloadsParser $parser;

    public function __construct(?DownloadsParser $parser = null)
    {
        $this->parser = $parser ?? new DownloadsParser();
    }

    public function handle(PackageInterface $package, Composer $composer, IOInterface $io): void
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
        foreach ($this->parser->parse($package, $basePath) as $extraFileHandler) {
            /** @var BaseHandler $extraFileHandler */
            if ($extraFileHandler->isInstalled($io)) {
                continue;
            }

            if ($first) {
                $io->write(sprintf('<info>Download extra files for <comment>%s</comment></info>', $package->getName()));
                $first = false;
            }

            $extraFileHandler->install($composer, $io);
        }
    }
}
