<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

abstract class ArchiveHandlerTestCase extends BaseHandlerTestCase
{
    public function getBinariesTests(): array
    {
        return [
            [null, []],
            [[], []],
            [['bin/file1'], ['bin/file1']],
            [['bin/file1', 'bin/file2'], ['bin/file1', 'bin/file2']],
        ];
    }

    public function getInvalidBinariesTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }
}
