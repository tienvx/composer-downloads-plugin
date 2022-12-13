<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Subpackage;

interface HandlerInterface
{
    public function install(Composer $composer, IOInterface $io): void;

    public function getSubpackage(): Subpackage;

    public function getTrackingFile(): string;

    public function getTrackingData(): array;

    public function getChecksum(): string;
}
