<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;

class PharHandler extends FileHandler
{
    public function download(Composer $composer, IOInterface $io): void
    {
        parent::download($composer, $io);

        if (Platform::isWindows()) {
            $proxy = $this->getTargetPath().'.bat';
            file_put_contents($proxy, '@php "%~dp0'.ProcessExecutor::escape(basename($proxy, '.bat')).'" %*');
        } else {
            chmod($this->getTargetPath(), 0777 ^ umask());
        }
    }
}
