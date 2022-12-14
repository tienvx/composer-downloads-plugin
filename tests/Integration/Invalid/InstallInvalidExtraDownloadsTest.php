<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

abstract class InstallInvalidExtraDownloadsTest extends CommandTestCase
{
    protected static function getComposerJson(): array
    {
        $json = parent::getComposerJson();
        $json['extra']['downloads'][static::getId()] = static::getExtraFile();

        return $json;
    }

    protected function getFilesFromProject(): array
    {
        return array_fill_keys(array_keys(parent::getFilesFromProject()), false);
    }

    protected function getExecutableFiles(): array
    {
        return $this->getExecutableFilesFromLibrary();
    }

    protected function getMissingExecutableFiles(): array
    {
        return array_keys($this->getExecutableFilesFromProject());
    }

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }

    protected function assertComposerErrorOutput(string $output): void
    {
        $this->assertStringContainsString(static::getErrorMessage(), $output);
    }

    abstract protected static function getId(): string;

    abstract protected static function getExtraFile(): array;

    abstract protected static function getErrorMessage(): string;
}
