<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoPlugin;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

abstract class CommandNoPluginTestCase extends CommandTestCase
{
    protected function shouldExistAfterCommand(): bool
    {
        return false;
    }

    protected function getExecutableFiles(): array
    {
        return [];
    }

    protected function getMissingExecutableFiles(): array
    {
        return array_keys(parent::getExecutableFiles());
    }
}
