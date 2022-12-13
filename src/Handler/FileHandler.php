<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Subpackage;

class FileHandler extends BaseHandler
{
    public const TMP_PREFIX = '.composer-extra-tmp-';

    protected Filesystem $filesystem;

    public function __construct(
        Subpackage $subpackage,
        ?BinariesInstaller $binariesInstaller = null,
        ?Filesystem $filesystem = null
    ) {
        parent::__construct($subpackage, $binariesInstaller);
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function getTrackingFile(): string
    {
        $id = $this->subpackage->getSubpackageName();
        $file = $id.'-'.md5($id).'.json';

        return
            \dirname($this->subpackage->getTargetPath()).
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    protected function download(Composer $composer, IOInterface $io): void
    {
        // We want to take advantage of the cache in composer's downloader, but it
        // doesn't put the file the spot we want, so we shuffle a bit.

        $target = $this->subpackage->getTargetPath();
        $downloadManager = $composer->getDownloadManager();

        // composer:v2
        if ($this->isComposerV2()) {
            $file = '';
            $promise = $downloadManager->download($this->subpackage, \dirname($target));
            $promise->then(static function ($res) use (&$file) {
                $file = $res;
            });
            $composer->getLoop()->wait([$promise]);
            // Look like Composer v2 doesn't care about $target above.
            // It download the file to "vendor/composer/tmp-[random-file-name]"
            // We need to move the file to where we want.
            $this->filesystem->rename($file, $target);
        }
        // composer:v1
        else {
            // Composer v1 empty the target directory. So we need to create new temporary directory.
            $tmpDir = \dirname($target).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
            $this->filesystem->ensureDirectoryExists($tmpDir);
            // Download manager doesn't return the file, so we ask file downloader to do it instead.
            $file = $downloadManager->getDownloader('file')->download($this->subpackage, $tmpDir);
            $this->filesystem->rename($file, $target);
            $this->filesystem->remove($tmpDir);
        }
    }
}
