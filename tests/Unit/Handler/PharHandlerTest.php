<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\PharHandler;

class PharHandlerTest extends FileHandlerTest
{
    protected function getHandlerClass(): string
    {
        return PharHandler::class;
    }

    protected function getChecksum(): string
    {
        return '86ce3e622db4ae557d3050172f8794b484c804649547d670485de10ce7041fd4';
    }

    protected function getSubpackageType(): string
    {
        return 'phar';
    }
}
