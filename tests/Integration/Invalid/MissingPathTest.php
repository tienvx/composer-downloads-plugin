<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class MissingPathTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'missing-path';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => null,
            'url' => 'http://localhost:8000/archive/presentation.tar.bz2',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "path" of extra file "missing-path" defined in package "test/project" is required.';
    }
}
