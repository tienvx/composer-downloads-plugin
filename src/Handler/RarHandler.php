<?php

namespace LastCall\DownloadsPlugin\Handler;

class RarHandler extends ArchiveHandler
{
    protected function getDistType(): string
    {
        return 'rar';
    }
}
