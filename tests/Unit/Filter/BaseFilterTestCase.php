<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Exception\UnexpectedValueException;
use LastCall\DownloadsPlugin\Filter\FilterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class BaseFilterTestCase extends TestCase
{
    protected PackageInterface|MockObject $parent;
    protected FilterInterface $filter;
    protected string $name = 'file-name';
    protected string $parentName = 'vendor/parent-package';
    protected string $parentPath = '/path/to/vendor/parent-package';

    protected function setUp(): void
    {
        $this->parent = $this->createMock(PackageInterface::class);
        $this->filter = $this->createFilter();
    }

    protected function expectUnexpectedValueException(string $attribute, string $reason): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectDeprecationMessage(sprintf('Attribute "%s" of extra file "%s" defined in package "%s" %s.', $attribute, $this->name, $this->parentName, $reason));
    }

    abstract protected function createFilter(): FilterInterface;
}
