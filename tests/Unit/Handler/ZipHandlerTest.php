<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\ZipHandler;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\ArchiveHandlerTestCase;

class ZipHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return ZipHandler::class;
    }

    protected function getDistType(): string
    {
        return 'zip';
    }

    protected function getChecksum(): string
    {
        return 'f6ddc54e6b646d0dd6bd392b28ddf3e914b49b5ec650f8d8db58149f6e1ea231';
    }
}
