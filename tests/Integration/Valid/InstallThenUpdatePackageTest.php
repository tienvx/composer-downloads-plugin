<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Valid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class InstallThenUpdatePackageTest extends CommandTestCase
{
    /**
     * @testWith [["install"]]
     *           [["update", "test/library"]]
     */
    public function testDownload(array $command): void
    {
        $this->runComposerCommandAndAssert($command);
    }
}
