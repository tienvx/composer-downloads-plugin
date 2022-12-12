<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Filter\ExecutableFilter;
use LastCall\DownloadsPlugin\Filter\FilterManager;
use LastCall\DownloadsPlugin\Filter\IgnoreFilter;
use LastCall\DownloadsPlugin\Filter\PathFilter;
use LastCall\DownloadsPlugin\Filter\TypeFilter;
use LastCall\DownloadsPlugin\Filter\UrlFilter;
use LastCall\DownloadsPlugin\Filter\VariablesFilter;
use LastCall\DownloadsPlugin\Filter\VersionFilter;
use LastCall\DownloadsPlugin\Handler\ArchiveHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterManagerTest extends TestCase
{
    private PackageInterface|MockObject $parent;
    private string $subpackageName = 'normal-file';
    private string $parentPath = 'path/to/vendor/parent-package';
    private string $handlerClass = ArchiveHandler::class;
    private FilterManager $manager;

    protected function setUp(): void
    {
        $this->parent = $this->createMock(PackageInterface::class);
        $this->manager = new FilterManager($this->subpackageName, $this->parent, $this->parentPath, $this->handlerClass);
    }

    public function getValidFilterTests(): array
    {
        return [
            ['path', PathFilter::class],
            ['url', UrlFilter::class],
            ['variables', VariablesFilter::class],
            ['version', VersionFilter::class],
            ['executable', ExecutableFilter::class],
            ['ignore', IgnoreFilter::class],
            ['type', TypeFilter::class],
        ];
    }

    /**
     * @dataProvider getValidFilterTests
     */
    public function testGetValidFilter(string $name, string $class): void
    {
        $this->assertInstanceOf($class, $this->manager->get($name));
    }

    public function testGetInvalidFilter(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('Filter "invalid" not found.');
        $this->manager->get('invalid');
    }
}
