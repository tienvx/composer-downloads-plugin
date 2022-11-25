<?php

namespace LastCall\DownloadsPlugin\Handler;

class XzHandler extends ArchiveHandler
{
    protected function getDistType(): string
    {
        return 'xz';
    }
}
