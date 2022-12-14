<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidExecutableTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-executable';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/executable',
            'url' => 'http://localhost:8000/archive/doc/v1.2.3/doc.tar.gz',
            'executable' => [
                '~/.local/file',
                '/usr/bin/file',
                '../file',
            ],
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "executable" of extra file "invalid-executable" defined in package "test/project" are not valid paths.';
    }
}
