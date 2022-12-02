<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

class BinariesInstallerTest extends TestCase
{
    protected ?FileSystem $fs = null;
    private IOInterface|MockObject $io;
    protected Subpackage|MockObject $subpackage;
    protected BinariesInstaller $installer;
    protected string $baseDir = '/path/to/files';
    protected string $subpackageName = 'vendor/package-name:executable-file';
    protected array $binaries = [
        false => 'file1',
        true => 'file2',
    ];

    protected function setUp(): void
    {
        $this->fs = new FileSystem(); // Keep virtual file system alive during test
        $this->io = $this->createMock(IOInterface::class);
        $this->subpackage = $this->createMock(Subpackage::class);
        $this->installer = new BinariesInstaller();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testInstall(): void
    {
        $this->subpackage->expects($this->once())->method('getBinaries')->willReturn($this->binaries);
        $this->fs->createDirectory($this->baseDir, true);
        foreach ($this->binaries as $hasProxy => $binary) {
            $path = $this->baseDir.\DIRECTORY_SEPARATOR.$binary;
            $content = implode(\PHP_EOL, [
                '#!/usr/bin/env php',
                '<?php',
                "echo 'Hello from php file!';",
            ]);
            $this->fs->createFile($path, $content);
            chmod($this->fs->path($path), 0600 ^ umask());
            if (\PHP_OS_FAMILY === 'Windows' && $hasProxy) {
                $proxy = $path.'.bat';
                $this->fs->createFile($proxy, 'proxy content');
            }
        }
        if (\PHP_OS_FAMILY === 'Windows') {
            $this->subpackage->expects($this->once())->method('getName')->willReturn($this->subpackageName);
            $this->io
                ->expects($this->once())
                ->method('writeError')
                ->with('    Skipped installation of bin '.$binary.'.bat proxy for package '.$this->subpackageName.': a .bat proxy was already installed');
        }
        $this->installer->install($this->subpackage, $this->fs->path($this->baseDir), $this->io);
        foreach ($this->binaries as $hasProxy => $binary) {
            $path = $this->baseDir.\DIRECTORY_SEPARATOR.$binary;
            if (\PHP_OS_FAMILY === 'Windows') {
                $proxy = $path.'.bat';
                if (!$hasProxy) {
                    $this->assertStringEqualsFile(
                        $this->fs->path($proxy),
                        '@php "%~dp0file1" %*'
                    );
                }
            } else {
                $this->assertTrue(is_executable($this->fs->path($path)));
            }
        }
    }
}
