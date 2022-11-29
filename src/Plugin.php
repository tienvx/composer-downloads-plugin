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
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private PackageHandler $handler;

    public function __construct(?PackageHandler $handler = null)
    {
        $this->handler = $handler ?? new PackageHandler();
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

        $rootPackage = $event->getComposer()->getPackage();
        $this->handler->handle($rootPackage, $event->getComposer(), $event->getIO());

        // Ensure that any other packages are properly reconciled.
        $localRepo = $event->getComposer()->getRepositoryManager()->getLocalRepository();
        foreach ($localRepo->getCanonicalPackages() as $package) {
            $this->handler->handle($package, $event->getComposer(), $event->getIO());
        }
    }

    public function installDownloads(PackageEvent $event): void
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();
        $this->handler->handle($package, $event->getComposer(), $event->getIO());
    }

    public function updateDownloads(PackageEvent $event): void
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();
        $this->handler->handle($package, $event->getComposer(), $event->getIO());
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
