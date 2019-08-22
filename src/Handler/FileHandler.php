<?php

namespace LastCall\ExtraFiles\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class FileHandler extends BaseHandler
{

    public function createSubpackage()
    {
        $pkg = parent::createSubpackage();
        $pkg->setDistType('file');
        return $pkg;
    }

    public function getTrackingFile()
    {
        $file = basename($this->extraFile['id']) . '-' . md5($this->extraFile['id']) . '.json';
        return
            dirname($this->getSubpackage()->getTargetDir()) .
            DIRECTORY_SEPARATOR . self::DOT_DIR .
            DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @param $basePath
     */
    public function download(Composer $composer, IOInterface $io, $basePath) {
        $downloadManager = $composer->getDownloadManager();
        $downloadManager->download($this->getSubpackage(), $this->getTargetDir($basePath));
    }

}
