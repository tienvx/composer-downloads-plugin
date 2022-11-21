<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Subpackage;

class PharHandler extends FileHandler
{
    protected function createSubpackage(): Subpackage
    {
        $pkg = parent::createSubpackage();
        $pkg->setBinaries([$this->extraFile['path']]);

        return $pkg;
    }

    public function download(Composer $composer, IOInterface $io): void
    {
        parent::download($composer, $io);
        $binaryInstaller = new BinaryInstaller($io, \dirname($this->getTargetPath()), 'auto');
        $binaryInstaller->installBinaries($this->getSubpackage(), $this->parentPath);
    }
}
