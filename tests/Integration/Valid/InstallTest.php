<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Valid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class InstallTest extends CommandTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }
}
