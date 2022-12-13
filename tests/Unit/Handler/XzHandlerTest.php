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

    protected function getSubpackageType(): string
    {
        return 'xz';
    }

    protected function getChecksum(): string
    {
        return 'e186c7bff32c734880a6a5d0559fe06321b00c5ed91063fea30219a630e03589';
    }
}
