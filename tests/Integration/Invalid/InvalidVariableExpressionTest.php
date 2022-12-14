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
                '{$valid-key}' => '["invalid value"]',
            ],
            'url' => 'http://localhost:8000/archive/doc/v1.2.3/doc.zip',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "variables" of extra file "invalid-variable-expression" defined in package "test/project" is invalid: Expression "["invalid value"]" should be evaluated to string, "array" given.';
    }
}
