<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class InstallTest extends CommandTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }
}
