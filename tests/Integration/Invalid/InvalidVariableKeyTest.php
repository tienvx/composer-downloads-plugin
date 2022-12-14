<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidVariableKeyTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-variable-key';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/variables',
            'variables' => [
                '$invalid-key' => 'valid value',
            ],
            'url' => 'http://localhost:8000/archive/doc/v1.2.3/doc.zip',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "variables" of extra file "invalid-variable-key" defined in package "test/project" is invalid: Variable key "$invalid-key" should be this format "{$variable-name}".';
    }
}
