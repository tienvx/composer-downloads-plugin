<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\UrlFilter;
use LastCall\DownloadsPlugin\Filter\VariablesFilter;
use PHPUnit\Framework\MockObject\MockObject;

class UrlFilterTest extends BaseFilterTestCase
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
        $this->expectUnexpectedValueException('url', 'is required');
        $this->filter->filter([]);
    }

    public function getInvalidUrlTypeTests(): array
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
     * @dataProvider getInvalidUrlTypeTests
     */
    public function testInvalidUrlType(mixed $invalidUrlType, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->variablesFilter->expects($this->never())->method('filter');
        $this->expectUnexpectedValueException('url', sprintf('must be string, "%s" given', $type));
        $this->filter->filter([
            'url' => $invalidUrlType,
        ]);
    }

    public function getInvalidUrlTests(): array
    {
        return [
            [''],
            ['C:\Programs\PHP\php.ini'],
            ['/var/www/project/uploads'],
        ];
    }

    /**
     * @dataProvider getInvalidUrlTests
     */
    public function testInvalidUrlFile(string $invalidUrl): void
    {
        $extraFile = [
            'url' => $invalidUrl,
        ];
        $this->variablesFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('url', 'is invalid url');
        $this->filter->filter($extraFile);
    }

    public function getFilterUrlTests(): array
    {
        return [
            ['http://example/file.rar', [], 'http://example/file.rar'],
            ['http://example/file-{$version}.rar', ['{$version}' => '1.2.3'], 'http://example/file-1.2.3.rar'],
        ];
    }

    /**
     * @dataProvider getFilterUrlTests
     */
    public function testFilterUrl(string $url, array $variables, string $expectedUrl): void
    {
        $extraFile = ['variables' => $variables, 'url' => $url];
        $this->variablesFilter->expects($this->once())->method('filter')->with($extraFile)->willReturn($variables);
        $this->parent->expects($this->never())->method('getName');
        $this->assertSame($expectedUrl, $this->filter->filter($extraFile));
        $this->assertSame($expectedUrl, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new UrlFilter($this->name, $this->parent, $this->variablesFilter);
    }
}
