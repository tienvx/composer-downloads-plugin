<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\TypeFilter;
use LastCall\DownloadsPlugin\Filter\UrlFilter;
use LastCall\DownloadsPlugin\Types;
use PHPUnit\Framework\MockObject\MockObject;

class TypeFilterTest extends BaseFilterTestCase
{
    private UrlFilter|MockObject $urlFilter;

    protected function setUp(): void
    {
        $this->urlFilter = $this->createMock(UrlFilter::class);
        parent::setUp();
    }

    public function getParseTypeFromUrlTests(): array
    {
        return [
            ['http://example.com/file.tar.gz', Types::TYPE_TAR],
            ['http://example.com/file.tar.bz2', Types::TYPE_TAR],
            ['http://example.com/file.tar.xz', Types::TYPE_XZ],
            ['http://example.com/file.zip', Types::TYPE_ZIP],
            ['http://example.com/file.rar', Types::TYPE_RAR],
            ['http://example.com/file.tgz', Types::TYPE_TAR],
            ['http://example.com/file.tar', Types::TYPE_TAR],
            ['http://example.com/file.gz', Types::TYPE_GZIP],
            ['http://example.com/file.phar', Types::TYPE_PHAR],
            ['http://example.com/file', Types::TYPE_FILE],
        ];
    }

    /**
     * @dataProvider getParseTypeFromUrlTests
     */
    public function testParseTypeFromUrl(string $url, string $expectedType): void
    {
        $extraFile = ['url' => $url];
        $this->urlFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($url);
        $this->assertSame($expectedType, $this->filter->filter($extraFile));
        $this->assertSame($expectedType, $this->filter->filter([]));
    }

    public function getInvalidTypeTests(): array
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
     * @dataProvider getInvalidTypeTests
     */
    public function testInvalidType(mixed $invalidType, string $type): void
    {
        $extraFile = [
            'type' => $invalidType,
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->urlFilter->expects($this->never())->method('filter');
        $this->expectUnexpectedValueException('type', sprintf('must be string, "%s" given', $type));
        $this->filter->filter($extraFile);
    }

    public function testNotSupportedType(): void
    {
        $extraFile = [
            'type' => 'not supported',
        ];
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->urlFilter->expects($this->never())->method('filter');
        $this->expectUnexpectedValueException('type', 'is not supported');
        $this->filter->filter($extraFile);
    }

    public function testFilterType(): void
    {
        $extraFile = ['type' => Types::TYPE_TAR];
        $this->urlFilter->expects($this->never())->method('filter');
        $this->assertSame(Types::TYPE_TAR, $this->filter->filter($extraFile));
        $this->assertSame(Types::TYPE_TAR, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new TypeFilter($this->name, $this->parent, $this->urlFilter);
    }
}
