<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\WorkingDir;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

abstract class CommandWorkingDirTestCase extends CommandTestCase
{
    protected static function shouldChangeDir(): bool
    {
        return false;
    }
}
