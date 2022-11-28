<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class UpdateTest extends CommandTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['update']);
    }
}
