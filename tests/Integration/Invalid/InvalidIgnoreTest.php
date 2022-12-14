<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidIgnoreTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-ignore';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/ignore',
            'url' => 'http://localhost:8000/archive/doc/v1.3.0/doc.tgz',
            'ignore' => '*',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "ignore" of extra file "invalid-ignore" defined in package "test/project" must be array, "string" given.';
    }
}
