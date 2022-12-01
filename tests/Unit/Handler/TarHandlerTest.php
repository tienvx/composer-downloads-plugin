<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\TarHandler;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\ArchiveHandlerTestCase;

class TarHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return TarHandler::class;
    }

    protected function getDistType(): string
    {
        return 'tar';
    }

    protected function getChecksum(): string
    {
        return '8edb7f661b55641bb6c4505f2bada397af779c3d76232bafe1d14b7fe4d5e527';
    }
}
