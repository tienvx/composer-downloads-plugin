<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Composer;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Subpackage;
use LastCall\DownloadsPlugin\SubpackageFactory;
use PHPUnit\Framework\TestCase;

class SubpackageFactoryTest extends TestCase
{
    private PackageInterface $parent;
    private SubpackageFactory $factory;
    private array $extra = [
        'downloads' => [
            '*' => [
                'path' => 'common/path/to/dir',
            ],
            'file1' => [
                'url' => 'http://example.com/file1.zip',
                'version' => '1.2.3',
            ],
            'file2' => [
                'url' => 'http://example.com/file2.tar.gz',
                'executable' => [
                    'file1',
                    'path/to/file2',
                ],
            ],
            'file3' => [
                'type' => 'xz',
                'url' => 'http://example.com/{$id}.xz',
                'ignore' => [
                    'dir/*',
                    '!dir/file1',
                ],
                'path' => 'path/to/dir',
            ],
        ],
    ];
    private string $parentPath = '/path/to/vendor/package-name';

    protected function setUp(): void
    {
        $this->parent = new Package('vendor/package-name', '1.0.0', 'v1.0.0');
        $this->parent->setExtra($this->extra);
        $this->factory = new SubpackageFactory();
    }

    public function testCreate(): void
    {
        $subpackages = $this->factory->create(
            $this->parent,
            $this->parentPath
        );
        $this->assertCount(3, $subpackages);
        $version = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0 ? 'dev-master' : '9999999-dev';
        $this->assertSubpackage(
            $subpackages[0],
            'file1',
            'zip',
            $version,
            '1.2.3',
            'http://example.com/file1.zip',
            'zip',
            'common/path/to/dir',
            [],
            []
        );
        $this->assertSubpackage(
            $subpackages[1],
            'file2',
            'tar',
            '1.0.0',
            'v1.0.0',
            'http://example.com/file2.tar.gz',
            'tar',
            'common/path/to/dir',
            [
                'file1',
                'path/to/file2',
            ],
            []
        );
        $this->assertSubpackage(
            $subpackages[2],
            'file3',
            'xz',
            '1.0.0',
            'v1.0.0',
            'http://example.com/file3.xz',
            'xz',
            'path/to/dir',
            [],
            [
                'dir/*',
                '!dir/file1',
            ]
        );
    }

    private function assertSubpackage(
        Subpackage $subpackage,
        string $subpackageName,
        string $subpackageType,
        string $version,
        string $prettyVersion,
        string $url,
        string $distType,
        string $path,
        array $executable,
        array $ignore
    ): void {
        $this->assertSame(sprintf('vendor/package-name:%s', $subpackageName), $subpackage->getName());
        $this->assertSame($version, $subpackage->getVersion());
        $this->assertSame($prettyVersion, $subpackage->getPrettyVersion());
        $this->assertSame($url, $subpackage->getDistUrl());
        $this->assertSame($distType, $subpackage->getDistType());
        $this->assertSame($path, $subpackage->getTargetDir());
        $this->assertSame('dist', $subpackage->getInstallationSource());
        $this->assertSame($subpackageName, $subpackage->getSubpackageName());
        $this->assertSame($executable, $subpackage->getExecutable());
        $this->assertSame($ignore, $subpackage->getIgnore());
        $this->assertSame($subpackageType, $subpackage->getSubpackageType());
        $this->assertSame($this->parentPath.\DIRECTORY_SEPARATOR.$path, $subpackage->getTargetPath());
    }
}
