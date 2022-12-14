<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class OutsidePathTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'outside-path';
    }

    protected static function getExtraFile(): array
    {
        return [
            'type' => 'file',
            'url' => 'http://localhost:8000/file/ipsum',
            'path' => '../files/file/ipsum',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "path" of extra file "outside-path" defined in package "test/project" must be inside relative to parent package\'s path.';
    }
}
