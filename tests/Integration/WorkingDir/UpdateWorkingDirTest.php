<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\WorkingDir;

class UpdateWorkingDirTest extends CommandWorkingDirTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['update',  '-d', self::getPathToTestDir()]);
    }
}
