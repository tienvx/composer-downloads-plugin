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

    protected function getTrackingFile(): string
    {
        return $this->targetPath.\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }
}
