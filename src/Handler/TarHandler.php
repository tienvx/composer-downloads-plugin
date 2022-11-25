<?php

namespace LastCall\DownloadsPlugin\Handler;

class TarHandler extends ArchiveHandler
{
    protected function getDistType(): string
    {
        return 'tar';
    }
}
