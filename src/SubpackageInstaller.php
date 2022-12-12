<?php

namespace LastCall\DownloadsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;

class SubpackageInstaller
{
    private HandlerInterface $handler;

    public function __construct(private Subpackage $subpackage, ?HandlerInterface $handler = null)
    {
        $this->handler = $handler ?? Types::createHandler($subpackage);
    }

    public function isInstalled(IOInterface $io): bool
    {
        $targetPath = $this->subpackage->getTargetPath();
        $trackingFile = $this->handler->getTrackingFile();

        if (file_exists($targetPath) && !file_exists($trackingFile)) {
            $io->write(
                sprintf(
                    '<info>Extra file <comment>%s</comment> has been locally overriden in <comment>%s</comment>. To reset it, delete and reinstall.</info>',
                    $this->subpackage->getName(),
                    $this->subpackage->getTargetDir()
                ),
                true
            );

            return true;
        }

        if (file_exists($targetPath) && file_exists($trackingFile)) {
            $meta = @json_decode(file_get_contents($trackingFile), 1, 512, \JSON_THROW_ON_ERROR);
            if (isset($meta['checksum']) && $meta['checksum'] === $this->handler->getChecksum()) {
                $io->write(
                    sprintf('<info>Skip extra file <comment>%s</comment></info>', $this->subpackage->getName()),
                    true,
                    IOInterface::VERY_VERBOSE
                );

                return true;
            }
        }

        return false;
    }

    public function install(Composer $composer, IOInterface $io): void
    {
        $io->write(
            sprintf('<info>Download extra file <comment>%s</comment></info>', $this->subpackage->getName()),
            true,
            IOInterface::VERBOSE
        );
        $this->handler->install($composer, $io);
        $this->createTrackingFile($io);
    }

    private function createTrackingFile(IOInterface $io): void
    {
        $io->write(
            sprintf('<info>Create tracking file for <comment>%s</comment></info>', $this->subpackage->getName()),
            true,
            IOInterface::VERY_VERBOSE
        );
        $trackingFile = $this->handler->getTrackingFile();
        if (!file_exists(\dirname($trackingFile))) {
            mkdir(\dirname($trackingFile), 0777, true);
        }
        file_put_contents($trackingFile, json_encode(
            $this->handler->getTrackingData(),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
        ));
    }
}
