<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoPlugin;

class UpdateNoPluginTest extends CommandNoPluginTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['update',  '--no-plugins']);
    }
}
