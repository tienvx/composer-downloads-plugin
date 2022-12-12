<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubpackageTest extends TestCase
{
    private PackageInterface|MockObject $parent;
    private string $parentName = 'vendor/package-name';
    private string $subpackageName = 'normal-file';
    private string $subpackageType = 'phar';
    private array $executable = [
        'file1',
        'path/to/file2',
    ];
    private array $ignore = [
        'dir/*',
        '!dir/file1',
    ];
    private string $url = 'http://example.com/file.zip';
    private string $path = 'path/to/dir';
    private string $version = '1.2.3.0';
    private string $prettyVersion = 'v1.2.3';
    private string $parentPath = '/path/to/vendor/package-name';

    protected function setUp(): void
    {
        $this->parent = $this->createMock(PackageInterface::class);
    }

    public function testInstance(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $subpackage = new Subpackage(
            $this->parent,
            $this->parentPath,
            $this->subpackageName,
            $this->subpackageType,
            $this->executable,
            $this->ignore,
            $this->url,
            $this->path,
            $this->version,
            $this->prettyVersion
        );
        $this->assertSame(sprintf('%s:%s', $this->parentName, $this->subpackageName), $subpackage->getName());
        $this->assertSame($this->version, $subpackage->getVersion());
        $this->assertSame($this->prettyVersion, $subpackage->getPrettyVersion());
        $this->assertSame($this->url, $subpackage->getDistUrl());
        $this->assertSame('file', $subpackage->getDistType());
        $this->assertSame($this->path, $subpackage->getTargetDir());
        $this->assertSame('dist', $subpackage->getInstallationSource());
        $this->assertSame($this->subpackageName, $subpackage->getSubpackageName());
        $this->assertSame($this->subpackageType, $subpackage->getSubpackageType());
        $this->assertSame($this->executable, $subpackage->getExecutable());
        $this->assertSame($this->ignore, $subpackage->getIgnore());
        $this->assertSame($this->parentPath, $subpackage->getParentPath());
        $this->assertSame($this->parentPath.\DIRECTORY_SEPARATOR.$this->path, $subpackage->getTargetPath());
    }
}
