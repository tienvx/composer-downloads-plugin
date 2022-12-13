<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\PathFilter;
use LastCall\DownloadsPlugin\Filter\VariablesFilter;
use PHPUnit\Framework\MockObject\MockObject;

class PathFilterTest extends BaseFilterTestCase
{
    private VariablesFilter|MockObject $variablesFilter;

    protected function setUp(): void
    {
        $this->variablesFilter = $this->createMock(VariablesFilter::class);
        parent::setUp();
    }

    public function testNotSet(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->variablesFilter->expects($this->never())->method('filter');
        $this->expectUnexpectedValueException('path', 'is required');
        $this->filter->filter([]);
    }

    public function getInvalidPathTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidPathTests
     */
    public function testInvalidPath(mixed $invalidPath, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->variablesFilter->expects($this->never())->method('filter');
        $this->expectUnexpectedValueException('path', sprintf('must be string, "%s" given', $type));
        $this->filter->filter([
            'path' => $invalidPath,
        ]);
    }

    public function getAbsolutePathTests(): array
    {
        return [
            ['C:\Programs\PHP\php.ini'],
            ['/var/www/project/uploads'],
        ];
    }

    /**
     * @dataProvider getAbsolutePathTests
     */
    public function testAbsolutePathFile(string $absolutePath): void
    {
        $extraFile = [
            'path' => $absolutePath,
        ];
        $this->variablesFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('path', 'must be relative path');
        $this->filter->filter($extraFile);
    }

    public function testOutsidePath(): void
    {
        $extraFile = [
            'path' => '../../other/place',
        ];
        $this->variablesFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('path', "must be inside relative to parent package's path");
        $this->filter->filter($extraFile);
    }

    public function getFilterPathTests(): array
    {
        return [
            ['path/to/file', [], 'path/to/file'],
            ['path/to/file/{$version}', ['{$version}' => '1.2.3'], 'path/to/file/1.2.3'],
        ];
    }

    /**
     * @dataProvider getFilterPathTests
     */
    public function testFilterPath(string $path, array $variables, string $expectedPath): void
    {
        $extraFile = ['variables' => $variables, 'path' => $path];
        $this->variablesFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($variables);
        $this->parent->expects($this->never())->method('getName');
        $this->assertSame($expectedPath, $this->filter->filter($extraFile));
        $this->assertSame($expectedPath, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new PathFilter($this->name, $this->parent, $this->parentPath, $this->variablesFilter);
    }
}
