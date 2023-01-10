<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidVariableExpressionTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-variable-expression';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/variables',
            'variables' => [
                '{$version}' => 'VERSION in ["dev", "test"] ? "0.0.1" : "1.0.0"',
            ],
            'url' => 'http://localhost:8000/archive/doc/v{$version}/doc.zip',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "variables" of extra file "invalid-variable-expression" defined in package "test/project" is invalid. There is an error while evaluating expression "VERSION in ["dev", "test"] ? "0.0.1" : "1.0.0"": unexpected end of string.';
    }
}
