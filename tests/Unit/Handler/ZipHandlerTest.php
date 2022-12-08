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
        return 'dee14fc8dcfc413b334ff218be5732736b5f9c07c3988bb8742d80f74f40ff0a';
    }
}
