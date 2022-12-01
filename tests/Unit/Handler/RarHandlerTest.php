<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\RarHandler;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\ArchiveHandlerTestCase;

class RarHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return RarHandler::class;
    }

    protected function getDistType(): string
    {
        return 'rar';
    }

    protected function getChecksum(): string
    {
        return '509c645de46584bf368f2e93ce8fb9f599f22124e60559fa9b0c43619f7d1feb';
    }
}
