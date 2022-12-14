<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidUrlSchemeTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-url-scheme';
    }

    protected static function getExtraFile(): array
    {
        return [
            'url' => 'file:///etc/passwd',
            'path' => 'files/credentials/passwd',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "url" of extra file "invalid-url-scheme" defined in package "test/project" has invalid scheme.';
    }
}
