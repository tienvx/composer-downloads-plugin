<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class UpdateWorkingDirTest extends CommandTestCase
{
    protected static bool $needChangeDir = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['update',  '-d', self::getPathToTestDir()]);
    }
}
