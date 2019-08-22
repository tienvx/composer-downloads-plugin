<?php

namespace LastCall\ExtraFiles\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\ExtraFiles\GlobCleaner;

class ArchiveHandler extends BaseHandler
{

    public function createSubpackage()
    {
        $pkg = parent::createSubpackage();
        $pkg->setDistType($this->parseDistType($this->extraFile['url']));
        return $pkg;
    }

    protected function parseDistType($url)
    {
        $parts = parse_url($url);
        $filename = pathinfo($parts['path'], PATHINFO_BASENAME);
        if (preg_match('/\.zip$/', $filename)) {
            return 'zip';
        } elseif (preg_match('/\.(tar\.gz|tgz)$/', $filename)) {
            return 'tar';
        } else {
            throw new \RuntimeException("Failed to determine archive type for $filename");
        }
    }

    public function getTrackingFile()
    {
        $file = basename($this->extraFile['id']) . '-' . md5($this->extraFile['id']) . '.json';
        return
            $this->getSubpackage()->getTargetDir() .
            DIRECTORY_SEPARATOR . self::DOT_DIR .
            DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return string[]|NULL
     *   List of files to exclude. Use '**' to match subdirectories.
     *   Ex: ['.gitignore', '*.md']
     */
    public function findIgnores()
    {
        return isset($this->extraFile['ignore'])
            ? $this->extraFile['ignore']
            : NULL;
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @param $basePath
     */
    public function download(Composer $composer, IOInterface $io, $basePath)
    {
        $targetPath = $this->getTargetDir($basePath);
        $downloadManager = $composer->getDownloadManager();
        $downloadManager->download($this->getSubpackage(), $targetPath);
        GlobCleaner::clean($io, $targetPath, $this->findIgnores());
    }

}
