<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\ExecutableFilter;
use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\PathFilter;
use LastCall\DownloadsPlugin\Filter\TypeFilter;
use LastCall\DownloadsPlugin\Types;
use PHPUnit\Framework\MockObject\MockObject;

class ExecutableFilterTest extends BaseFilterTestCase
{
    private PathFilter|MockObject $pathFilter;
    private TypeFilter|MockObject $typeFilter;
    private string $path = 'path/to/file';

    protected function setUp(): void
    {
        $this->pathFilter = $this->createMock(PathFilter::class);
        $this->typeFilter = $this->createMock(TypeFilter::class);
        parent::setUp();
    }

    public function getEmptyExecutableTests(): array
    {
        return [
            [[]],
            [['executable' => []]],
        ];
    }

    /**
     * @dataProvider getEmptyExecutableTests
     */
    public function testEmptyExecutable(array $extraFile): void
    {
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_ZIP);
        $this->assertSame([], $this->filter->filter($extraFile));
    }

    public function getInvalidExecutableArchiveTypeTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidExecutableArchiveTypeTests
     */
    public function testInvalidExecutableArchiveType(mixed $invalidExecutable, string $type): void
    {
        $extraFile = [
            'executable' => $invalidExecutable,
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_ZIP);
        $this->expectUnexpectedValueException('executable', sprintf('must be array, "%s" given', $type));
        $this->filter->filter($extraFile);
    }

    public function testInvalidExecutableArchivePaths(): void
    {
        $extraFile = [
            'executable' => [
                ['not a string'],
                '/root/path/to/file',
                '../../outside/path/to/file',
            ],
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_ZIP);
        $this->expectUnexpectedValueException('executable', 'are not valid paths');
        $this->filter->filter($extraFile);
    }

    public function getInvalidExecutableFileTests(): array
    {
        return [
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidExecutableFileTests
     */
    public function testInvalidExecutableFile(mixed $invalidExecutable, string $type): void
    {
        $extraFile = [
            'executable' => $invalidExecutable,
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_FILE);
        $this->expectUnexpectedValueException('executable', sprintf('must be boolean, "%s" given', $type));
        $this->filter->filter($extraFile);
    }

    public function getInvalidExecutablePharTests(): array
    {
        return [
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidExecutablePharTests
     */
    public function testInvalidExecutablePhar(mixed $invalidExecutable, string $type): void
    {
        $extraFile = [
            'executable' => $invalidExecutable,
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_PHAR);
        $this->expectUnexpectedValueException('executable', sprintf('must be true, "%s" given', $type));
        $this->filter->filter($extraFile);
    }

    public function getFilterExecutableFileTests(): array
    {
        return [
            [Types::TYPE_FILE],
            [Types::TYPE_PHAR],
        ];
    }

    /**
     * @dataProvider getFilterExecutableFileTests
     */
    public function testFilterExecutableFile(string $type): void
    {
        $extraFile = ['executable' => true, 'path' => $this->path];
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($type);
        $expectedValue = [$this->path];
        $this->assertSame($expectedValue, $this->filter->filter($extraFile));
        $this->assertSame($expectedValue, $this->filter->filter([]));
    }

    public function testFilterExecutableArchive(): void
    {
        $executable = ['path/to/file', 'path/to/another/file'];
        $extraFile = ['executable' => $executable];
        $this->pathFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($this->path);
        $this->typeFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn(Types::TYPE_ZIP);
        $this->assertSame($executable, $this->filter->filter($extraFile));
        $this->assertSame($executable, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new ExecutableFilter($this->name, $this->parent, $this->parentPath, $this->typeFilter, $this->pathFilter);
    }
}
