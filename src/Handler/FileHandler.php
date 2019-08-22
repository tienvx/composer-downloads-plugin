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
            dirname($this->getTargetDir()) .
            DIRECTORY_SEPARATOR . self::DOT_DIR .
            DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function download(Composer $composer, IOInterface $io) {
        $downloadManager = $composer->getDownloadManager();
        $downloadManager->download($this->getSubpackage(), $this->getTargetDir());
    }

}
