<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\GlobCleaner;
use LastCall\DownloadsPlugin\Subpackage;

abstract class ArchiveHandler extends BaseHandler
{
    protected GlobCleaner $cleaner;

    public function __construct(
        Subpackage $subpackage,
        ?BinariesInstaller $binariesInstaller = null,
        ?GlobCleaner $cleaner = null
    ) {
        parent::__construct($subpackage, $binariesInstaller);
        $this->cleaner = $cleaner ?? new GlobCleaner();
    }

    public function getTrackingFile(): string
    {
        $id = $this->subpackage->getSubpackageName();
        $file = basename($id).'-'.md5($id).'.json';

        return
            $this->subpackage->getTargetPath().
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    public function getTrackingData(): array
    {
        return ['ignore' => $this->subpackage->getIgnore()] + parent::getTrackingData();
    }

    protected function getChecksumData(): array
    {
        $ignore = array_values($this->subpackage->getIgnore());
        sort($ignore);

        return ['ignore' => $ignore] + parent::getChecksumData();
    }

    protected function download(Composer $composer, IOInterface $io): void
    {
        $targetPath = $this->subpackage->getTargetPath();
        $downloadManager = $composer->getDownloadManager();

        // In composer:v2, download and extract were separated.
        if ($this->isComposerV2()) {
            $promise = $downloadManager->download($this->subpackage, $targetPath);
            $composer->getLoop()->wait([$promise]);
            $promise = $downloadManager->install($this->subpackage, $targetPath);
            $composer->getLoop()->wait([$promise]);
        } else {
            $downloadManager->download($this->subpackage, $targetPath);
        }
        $this->cleaner->clean($targetPath, $this->subpackage->getIgnore());
    }
}
