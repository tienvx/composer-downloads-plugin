<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

class FileHandler extends BaseHandler
{
    public const TMP_PREFIX = '.composer-extra-tmp-';

    protected function getTrackingFile(): string
    {
        $file = basename($this->extraFile['id']).'-'.md5($this->extraFile['id']).'.json';

        return
            \dirname($this->getTargetPath()).
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    protected function download(Composer $composer, IOInterface $io): void
    {
        // We want to take advantage of the cache in composer's downloader, but it
        // doesn't put the file the spot we want, so we shuffle a bit.

        $cfs = new Filesystem();
        $target = $this->getTargetPath();
        $tmpDir = \dirname($target).\DIRECTORY_SEPARATOR.self::TMP_PREFIX.basename($target);

        if (file_exists($tmpDir)) {
            $cfs->remove($tmpDir);
        }
        if (file_exists($target)) {
            $cfs->remove($target);
        }

        $pkg = clone $this->getSubpackage();
        $pkg->setTargetDir($tmpDir);
        $downloadManager = $composer->getDownloadManager();
        // composer:v2
        if ($this->isComposerV2()) {
            $file = '';
            $promise = $downloadManager->download($pkg, $tmpDir);
            $promise->then(static function ($res) use (&$file) {
                $file = $res;
            });
            $composer->getLoop()->wait([$promise]);
            $cfs->rename($file, $target);
            $cfs->remove($tmpDir);
        }
        // composer:v1
        else {
            $downloadManager->download($pkg, $tmpDir);
            foreach ((array) glob("$tmpDir/*") as $file) {
                if (is_file($file)) {
                    $cfs->rename($file, $target);
                    $cfs->remove($tmpDir);
                    break;
                }
            }
        }
    }

    protected function getDistType(): string
    {
        return 'file';
    }

    protected function getBinaries(): array
    {
        if (isset($this->extraFile['executable']) && !\is_bool($this->extraFile['executable'])) {
            throw new \UnexpectedValueException(sprintf('Attribute "executable" of extra file "%s" defined in package "%s" must be boolean, "%s" given.', $this->extraFile['id'], $this->parent->getId(), get_debug_type($this->extraFile['executable'])));
        }

        return empty($this->extraFile['executable']) ? [] : [$this->extraFile['path']];
    }
}
