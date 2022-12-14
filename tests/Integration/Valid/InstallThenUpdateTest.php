<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Valid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class InstallThenUpdateTest extends CommandTestCase
{
    /**
     * @testWith ["install"]
     *           ["update"]
     */
    public function testDownload(string $command): void
    {
        $this->runComposerCommandAndAssert([$command]);
    }
}
