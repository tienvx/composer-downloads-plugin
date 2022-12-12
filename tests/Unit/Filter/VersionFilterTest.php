<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use Composer\Package\RootPackage;
use Composer\Package\Version\VersionParser;
use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\VersionFilter;
use PHPUnit\Framework\MockObject\MockObject;

class VersionFilterTest extends BaseFilterTestCase
{
    private VersionParser|MockObject $versionParser;
    private string $version = '1.2.3.0';
    private string $prettyVersion = 'v1.2.3';

    protected function setUp(): void
    {
        $this->versionParser = $this->createMock(VersionParser::class);
        parent::setUp();
    }

    public function getInvalidVersionTests(): array
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
     * @dataProvider getInvalidVersionTests
     */
    public function testInvalidVersion(mixed $invalidVersion, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->versionParser->expects($this->never())->method('normalize');
        $this->expectUnexpectedValueException('version', sprintf('must be string, "%s" given', $type));
        $this->filter->filter([
            'version' => $invalidVersion,
        ]);
    }

    public function testVersionFromParentPackage(): void
    {
        $this->parent->expects($this->once())->method('getVersion')->willReturn($this->version);
        $this->parent->expects($this->once())->method('getPrettyVersion')->willReturn($this->prettyVersion);
        $this->versionParser->expects($this->never())->method('normalize');
        $expectedValue = [$this->version, $this->prettyVersion];
        $this->assertSame($expectedValue, $this->filter->filter([]));
        $this->assertSame($expectedValue, $this->filter->filter(['cached']));
    }

    public function testVersionFromParentRootPackage(): void
    {
        $parent = new RootPackage('vendor/project-name', '1.0.0', 'v1.0.0');
        $filter = new VersionFilter($this->name, $parent, $this->versionParser);
        $this->parent->expects($this->never())->method('getVersion');
        $this->parent->expects($this->never())->method('getPrettyVersion');
        $this->versionParser->expects($this->once())->method('normalize')->with('dev-master')->willReturn('9999999-dev');
        $expectedValue = ['9999999-dev', 'dev-master'];
        $this->assertSame($expectedValue, $filter->filter([]));
        $this->assertSame($expectedValue, $filter->filter(['cached']));
    }

    public function testCustomVersion(): void
    {
        $this->parent->expects($this->never())->method('getVersion');
        $this->parent->expects($this->never())->method('getPrettyVersion');
        $this->versionParser->expects($this->once())->method('normalize')->with('dev-master')->willReturn('9999999-dev');
        $expectedValue = ['9999999-dev', '1.2.3'];
        $this->assertSame($expectedValue, $this->filter->filter(['version' => '1.2.3']));
        $this->assertSame($expectedValue, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new VersionFilter($this->name, $this->parent, $this->versionParser);
    }
}
