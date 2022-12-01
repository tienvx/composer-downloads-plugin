<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\XzHandler;
use LastCall\DownloadsPlugin\Tests\Unit\Handler\ArchiveHandlerTestCase;

class XzHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return XzHandler::class;
    }

    protected function getDistType(): string
    {
        return 'xz';
    }

    protected function getChecksum(): string
    {
        return 'cbc8a1dcb56f869d991ed5cdb589ab46d7cb483af75067b50611d875f03bdbeb';
    }
}
