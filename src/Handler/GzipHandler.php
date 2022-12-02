<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use LastCall\DownloadsPlugin\BinariesInstaller;

class GzipHandler extends FileHandler
{
    protected ProcessExecutor $process;

    public function __construct(
        PackageInterface $parent,
        string $parentPath,
        array $extraFile,
        ?BinariesInstaller $binariesInstaller = null,
        ?Filesystem $filesystem = null,
        ?ProcessExecutor $process = null
    ) {
        parent::__construct($parent, $parentPath, $extraFile, $binariesInstaller, $filesystem);
        $this->process = $process ?? new ProcessExecutor();
    }

    protected function getTargetFilePath(): string
    {
        return $this->getTargetPath().'.gz';
    }

    protected function download(Composer $composer, IOInterface $io): void
    {
        parent::download($composer, $io);
        // Target file is still gzip file, need to be decompressed.
        $command = 'gzip -d '.ProcessExecutor::escape($this->getTargetFilePath());
        if (0 !== $this->process->execute($command)) {
            $processError = 'Failed to execute '.$command."\n\n".$this->process->getErrorOutput();
            throw new \RuntimeException($processError);
        }
    }
}
