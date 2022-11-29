<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

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
