<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\GlobCleaner;
use LastCall\DownloadsPlugin\Subpackage;

abstract class ArchiveHandler extends BaseHandler
{
    protected function createSubpackage(): Subpackage
    {
        $pkg = parent::createSubpackage();
        $pkg->setDistType($this->getDistType());

        return $pkg;
    }

    public function getTrackingFile(): string
    {
        $file = basename($this->extraFile['id']).'-'.md5($this->extraFile['id']).'.json';

        return
            $this->getTargetPath().
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    public function createTrackingData(): array
    {
        $meta = parent::createTrackingData();
        $meta['ignore'] = $this->findIgnores();

        return $meta;
    }

    public function getChecksum(): string
    {
        $ignore = array_values($this->findIgnores());
        sort($ignore);

        return hash('sha256', parent::getChecksum().serialize($ignore));
    }

    /**
     * @return string[] List of files to exclude. Use '**' to match subdirectories.
     *                  Ex: ['.gitignore', '*.md']
     */
    private function findIgnores(): array
    {
        if (isset($this->extraFile['ignore']) && !\is_array($this->extraFile['ignore'])) {
            throw new \UnexpectedValueException(sprintf('Attribute "ignore" of extra file "%s" defined in package "%s" must be array, "%s" given.', $this->extraFile['id'], $this->parent->getId(), get_debug_type($this->extraFile['ignore'])));
        }

        return $this->extraFile['ignore'] ?? [];
    }

    public function download(Composer $composer, IOInterface $io): void
    {
        $targetPath = $this->getTargetPath();
        $downloadManager = $composer->getDownloadManager();

        // In composer:v2, download and extract were separated.
        if ($this->isComposerV2()) {
            $promise = $downloadManager->download($this->getSubpackage(), $targetPath);
            $composer->getLoop()->wait([$promise]);
            $promise = $downloadManager->install($this->getSubpackage(), $targetPath);
            $composer->getLoop()->wait([$promise]);
        } else {
            $downloadManager->download($this->getSubpackage(), $targetPath);
        }
        GlobCleaner::clean($io, $targetPath, $this->findIgnores());
    }

    abstract protected function getDistType(): string;
}
