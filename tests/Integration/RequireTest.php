<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

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
