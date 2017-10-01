<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ExtraFiles;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /** @var Composer */
    private $composer;
    /** @var IOInterface */
    private $io;

    private $parser;

    public function __construct()
    {
        $this->parser = new ExtraFilesParser();
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'installExtraFiles',
            PackageEvents::POST_PACKAGE_UPDATE => 'installExtraFiles'
        ];
    }

    public function installExtraFiles(PackageEvent $event)
    {
        $downloadManager = $this->composer->getDownloadManager();
        $installManager = $this->composer->getInstallationManager();

        foreach ($event->getInstalledRepo()->getPackages() as $package) {
            // @todo: Move this logic into an InstallerInterface.
            foreach ($this->parser->parse($package) as $extraFile) {
                $path = $installManager->getInstallPath($package).'/'.$extraFile->getTargetDir();
                $downloadManager->download($extraFile, $path);
            }
        }
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
}
