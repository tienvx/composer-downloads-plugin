<?php

namespace LastCall\DownloadsPlugin\Handler;

class PharHandler extends FileHandler
{
    protected function getBinaries(): array
    {
        return [$this->extraFile['path']];
    }
}
