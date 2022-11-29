<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class RequireTest extends CommandTestCase
{
    protected static bool $requireLibrary = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['require', 'test/library']);
    }
}
