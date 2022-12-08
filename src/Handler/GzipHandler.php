<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class GzipHandler extends FileHandler
{
    protected function download(Composer $composer, IOInterface $io): void
    {
        $tmpDir = \dirname($this->getTargetPath()).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
        $targetName = pathinfo($this->getSubpackage()->getDistUrl(), \PATHINFO_FILENAME);
        $downloadManager = $composer->getDownloadManager();

        $this->filesystem->ensureDirectoryExists($tmpDir);
        // In composer:v2, download and extract were separated.
        if ($this->isComposerV2()) {
            $promise = $downloadManager->download($this->getSubpackage(), $tmpDir);
            $composer->getLoop()->wait([$promise]);
            $promise = $downloadManager->install($this->getSubpackage(), $tmpDir);
            $composer->getLoop()->wait([$promise]);
        } else {
            $downloadManager->download($this->getSubpackage(), $tmpDir);
        }
        $this->filesystem->rename($tmpDir.\DIRECTORY_SEPARATOR.$targetName, $this->getTargetPath());
        $this->filesystem->remove($tmpDir);
    }

    protected function getDistType(): string
    {
        return 'gzip';
    }
}
