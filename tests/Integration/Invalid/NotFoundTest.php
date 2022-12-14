<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

use Symfony\Component\Process\Exception\ProcessFailedException;

class NotFoundTest extends InstallInvalidExtraDownloadsTest
{
    public function testDownload(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessageMatches('/The "http:\/\/localhost:8000\/not-found\.zip" file could not be downloaded/');
        $this->runComposerCommandAndAssert(['install']);
    }

    protected static function getId(): string
    {
        return 'not-found';
    }

    protected static function getExtraFile(): array
    {
        return [
            'url' => 'http://localhost:8000/not-found.zip',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return '';
    }
}
