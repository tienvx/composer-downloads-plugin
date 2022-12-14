<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidUrlTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-url';
    }

    protected static function getExtraFile(): array
    {
        return [
            'url' => '/etc/passwd',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "url" of extra file "invalid-url" defined in package "test/project" is invalid url.';
    }
}
