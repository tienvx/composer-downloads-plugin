<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;

class SubpackageInstaller
{
    public function isInstalled(HandlerInterface $handler, IOInterface $io): bool
    {
        $subpackage = $handler->getSubpackage();
        $targetPath = $handler->getTargetPath();
        $trackingFile = $handler->getTrackingFile();

        if (file_exists($targetPath) && !file_exists($trackingFile)) {
            $io->write(sprintf('<info>Extra file <comment>%s</comment> has been locally overriden in <comment>%s</comment>. To reset it, delete and reinstall.</info>', $subpackage->getName(), $subpackage->getTargetDir()), true);

            return true;
        }

        if (file_exists($targetPath) && file_exists($trackingFile)) {
            $meta = @json_decode(file_get_contents($trackingFile), 1, 512, \JSON_THROW_ON_ERROR);
            if (isset($meta['checksum']) && $meta['checksum'] === $handler->getChecksum()) {
                $io->write(sprintf('<info>Skip extra file <comment>%s</comment></info>', $subpackage->getName()), true, IOInterface::VERY_VERBOSE);

                return true;
            }
        }

        return false;
    }

    public function install(HandlerInterface $handler, Composer $composer, IOInterface $io): void
    {
        $subpackage = $handler->getSubpackage();
        $io->write(sprintf('<info>Download extra file <comment>%s</comment></info>', $subpackage->getName()), true, IOInterface::VERBOSE);
        $handler->install($composer, $io);
        $this->createTrackingFile($handler, $io);
    }

    private function createTrackingFile(HandlerInterface $handler, IOInterface $io): void
    {
        $subpackage = $handler->getSubpackage();
        $io->write(sprintf('<info>Create tracking file for <comment>%s</comment></info>', $subpackage->getName()), true, IOInterface::VERY_VERBOSE);
        $trackingFile = $handler->getTrackingFile();
        if (!file_exists(\dirname($trackingFile))) {
            mkdir(\dirname($trackingFile), 0777, true);
        }
        file_put_contents($trackingFile, json_encode(
            $handler->getTrackingData(),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
        ));
    }
}
