<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class MissingUrlTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'missing-url';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/missing-url',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "url" of extra file "missing-url" defined in package "test/project" is required.';
    }
}
