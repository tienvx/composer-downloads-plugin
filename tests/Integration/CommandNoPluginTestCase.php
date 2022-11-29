<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

use Symfony\Component\Process\Process;

abstract class CommandNoPluginTestCase extends CommandTestCase
{
    protected function assertFiles(bool $exist = true): void
    {
        foreach ($this->getFiles() as $file => $sha256) {
            $this->assertFileDoesNotExist(self::getPathToTestDir($file));
        }
    }

    protected function assertExecutable(): void
    {
        foreach ($this->getExecutableFiles() as $file => $output) {
            $process = new Process([self::getPathToTestDir($file)]);
            $process->run();
            $this->assertSame('', $process->getOutput());
            $exitCodeMap = [
                'Windows' => 1,
                'Darwin' => 126,
                'Linux' => 127,
            ];
            $this->assertSame($exitCodeMap[\PHP_OS_FAMILY], $process->getExitCode());
        }
    }
}
