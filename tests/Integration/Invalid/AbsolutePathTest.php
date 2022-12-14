<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class AbsolutePathTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'absolute-path';
    }

    protected static function getExtraFile(): array
    {
        return [
            'type' => 'phar',
            'url' => 'http://localhost:8000/phar/hello.phar',
            'path' => '/usr/local/bin/hello',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "path" of extra file "absolute-path" defined in package "test/project" must be relative path.';
    }
}
