<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\WorkingDir;

class InstallWorkingDirTest extends CommandWorkingDirTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install', '-d', self::getPathToTestDir()]);
    }
}
