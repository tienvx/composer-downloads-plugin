<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\ProcessExecutor;

class GzipHandler extends FileHandler
{
    private ?string $target = null;

    public function getTargetPath(): string
    {
        return $this->target ?? parent::getTargetPath();
    }

    public function download(Composer $composer, IOInterface $io): void
    {
        $this->target = $gzip = $this->getTargetPath() . '.gz';
        parent::download($composer, $io);
        $this->target = null;
        // Target file is still gzip file, need to be decompressed.
        $process = new ProcessExecutor($io);
        $command = 'gzip -d '.ProcessExecutor::escape($gzip);
        if (0 !== $process->execute($command)) {
            $processError = 'Failed to execute '.$command."\n\n".$process->getErrorOutput();
            throw new \RuntimeException($processError);
        }
    }
}
