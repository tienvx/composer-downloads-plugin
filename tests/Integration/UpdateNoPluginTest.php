<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class UpdateNoPluginTest extends CommandNoPluginTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['update',  '--no-plugins']);
    }
}
