<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Valid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class RequireTest extends CommandTestCase
{
    protected static function getComposerJson(): array
    {
        return [
            'require' => [
                'tienvx/composer-downloads-plugin' => '@dev',
            ],
        ] + parent::getComposerJson();
    }

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['require', 'test/library']);
    }
}
