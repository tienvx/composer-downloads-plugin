<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use LastCall\DownloadsPlugin\Handler\BaseHandler;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private ?Composer $composer = null;
    private ?IOInterface $io = null;

    private DownloadsParser $parser;

    public function __construct()
    {
        $this->parser = new DownloadsParser();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => ['installDownloads', 10],
            PackageEvents::POST_PACKAGE_UPDATE => ['updateDownloads', 10],
            ScriptEvents::POST_INSTALL_CMD => ['installDownloadsRoot', 10],
            ScriptEvents::POST_UPDATE_CMD => ['installDownloadsRoot', 10],
        ];
    }

    public function installDownloadsRoot(Event $event): void
    {
        $cwd = getcwd();
        if (!\is_string($cwd)) {
            throw new \RuntimeException('Failed to get current working directory');
        }

        $rootPackage = $this->composer->getPackage();
        $this->installUpdateDownloads($cwd, $rootPackage);

        // Ensure that any other packages are properly reconciled.
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        $installationManager = $this->composer->getInstallationManager();
        foreach ($localRepo->getCanonicalPackages() as $package) {
            /** @var \Composer\Package\PackageInterface $package */
            if (!empty($package->getExtra()['downloads'])) {
                $this->installUpdateDownloads($installationManager->getInstallPath($package), $package);
                $installationManager->ensureBinariesPresence($package);
            }
        }
    }

    public function installDownloads(PackageEvent $event): void
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();
        $installationManager = $this->composer->getInstallationManager();
        $this->installUpdateDownloads($installationManager->getInstallPath($package), $package);
    }

    public function updateDownloads(PackageEvent $event): void
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();
        $installationManager = $this->composer->getInstallationManager();
        $this->installUpdateDownloads($installationManager->getInstallPath($package), $package);
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // @todo determine if any operation required.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // @todo determine if any operation required.
    }

    protected function installUpdateDownloads(string $basePath, PackageInterface $package): void
    {
        $first = true;
        foreach ($this->parser->parse($package, $basePath) as $extraFileHandler) {
            /** @var BaseHandler $extraFileHandler */
            $extraFilePkg = $extraFileHandler->getSubpackage();
            $targetPath = $extraFileHandler->getTargetPath();
            $trackingFile = $extraFileHandler->getTrackingFile();

            if (file_exists($targetPath) && !file_exists($trackingFile)) {
                $this->io->write(sprintf('<info>Extra file <comment>%s</comment> has been locally overriden in <comment>%s</comment>. To reset it, delete and reinstall.</info>', $extraFilePkg->getName(), $extraFilePkg->getTargetDir()), true);
                continue;
            }

            if (file_exists($targetPath) && file_exists($trackingFile)) {
                $meta = @json_decode(file_get_contents($trackingFile), 1, 512, \JSON_THROW_ON_ERROR);
                if (isset($meta['checksum']) && $meta['checksum'] === $extraFileHandler->getChecksum()) {
                    $this->io->write(sprintf('<info>Skip extra file <comment>%s</comment></info>', $extraFilePkg->getName()), true, IOInterface::VERY_VERBOSE);
                    continue;
                }
            }

            if ($first) {
                $this->io->write(sprintf('<info>Download extra files for <comment>%s</comment></info>', $package->getName()));
                $first = false;
            }

            $this->io->write(sprintf('<info>Download extra file <comment>%s</comment></info>', $extraFilePkg->getName()), true, IOInterface::VERBOSE);
            $extraFileHandler->download($this->composer, $this->io);
            $extraFileHandler->install($this->io);
        }
    }
}
