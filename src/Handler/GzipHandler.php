<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;

class GzipHandler extends FileHandler
{
    public function download(Composer $composer, IOInterface $io): void
    {
        parent::download($composer, $io);
        // Target file is still gzip file, need to be decompressed.
        $target = $this->getSubpackage()->getTargetDir();
        $gzip = $target.'.gz';
        $cfs = new Filesystem();
        $cfs->rename($target, $gzip);
        $process = new ProcessExecutor($io);
        $command = 'gzip -d '.ProcessExecutor::escape($gzip);
        $process->execute($command);
    }
}
