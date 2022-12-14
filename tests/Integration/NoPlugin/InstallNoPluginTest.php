<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoPlugin;

class InstallNoPluginTest extends CommandNoPluginTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install', '--no-plugins']);
    }
}
