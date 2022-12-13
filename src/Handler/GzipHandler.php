<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class GzipHandler extends FileHandler
{
    protected function download(Composer $composer, IOInterface $io): void
    {
        $tmpDir = \dirname($this->subpackage->getTargetPath()).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
        $targetName = pathinfo($this->subpackage->getDistUrl(), \PATHINFO_FILENAME);
        $downloadManager = $composer->getDownloadManager();

        $this->filesystem->ensureDirectoryExists($tmpDir);
        // In composer:v2, download and extract were separated.
        if ($this->isComposerV2()) {
            $promise = $downloadManager->download($this->subpackage, $tmpDir);
            $composer->getLoop()->wait([$promise]);
            $promise = $downloadManager->install($this->subpackage, $tmpDir);
            $composer->getLoop()->wait([$promise]);
        } else {
            $downloadManager->download($this->subpackage, $tmpDir);
        }
        $this->filesystem->rename($tmpDir.\DIRECTORY_SEPARATOR.$targetName, $this->subpackage->getTargetPath());
        $this->filesystem->remove($tmpDir);
    }
}
