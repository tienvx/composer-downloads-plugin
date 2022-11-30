<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\ProcessExecutor;

class GzipHandler extends FileHandler
{
    protected function getTargetFilePath(): string
    {
        return $this->getTargetPath().'.gz';
    }

    protected function download(Composer $composer, IOInterface $io): void
    {
        parent::download($composer, $io);
        // Target file is still gzip file, need to be decompressed.
        $process = new ProcessExecutor($io);
        $command = 'gzip -d '.ProcessExecutor::escape($this->getTargetFilePath());
        if (0 !== $process->execute($command)) {
            $processError = 'Failed to execute '.$command."\n\n".$process->getErrorOutput();
            throw new \RuntimeException($processError);
        }
    }
}
