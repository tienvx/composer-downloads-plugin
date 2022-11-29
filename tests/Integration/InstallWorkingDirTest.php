<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class InstallWorkingDirTest extends CommandTestCase
{
    protected static bool $needChangeDir = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install', '-d', self::getPathToTestDir()]);
    }
}
