<?php

namespace LastCall\DownloadsPlugin\Handler;

class ZipHandler extends ArchiveHandler
{
    protected function getDistType(): string
    {
        return 'zip';
    }
}
