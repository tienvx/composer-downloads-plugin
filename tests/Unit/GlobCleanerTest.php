<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\GlobCleaner;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

class GlobCleanerTest extends TestCase
{
    private const FILE = 'file';
    private const DIR = 'dir';
    private const FILES_AND_DIRECTORIES = [
        '/dir1' => self::DIR,
        '/dir1/file1.txt' => self::FILE,
        '/dir1/file2.csv' => self::FILE,
        '/dir1/file3.json' => self::FILE,
        '/dir1/dir11' => self::DIR,
        '/dir1/dir11/file1.doc' => self::FILE,
        '/dir1/dir11/file2.xls' => self::FILE,
        '/dir1/dir11/file3.ppt' => self::FILE,
        '/dir1/dir12' => self::DIR,
        '/dir1/dir12/file1.php' => self::FILE,
        '/dir2' => self::DIR,
        '/dir2/dir21' => self::DIR,
        '/dir2/dir21/file1.png' => self::FILE,
        '/dir2/dir21/file2.jpg' => self::FILE,
        '/dir2/dir22' => self::DIR,
        '/dir2/dir22/.composer-downloads' => self::DIR,
        '/dir2/dir22/.composer-downloads/file1.json' => self::FILE,
        '/dir3' => self::DIR,
        '/file1.exe' => self::FILE,
        '/file2.bat' => self::FILE,
    ];

    protected ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = new FileSystem(); // Keep virtual file system alive during test
        $this->createFilesAndDirectories();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testEmptyIgnore(): void
    {
        GlobCleaner::clean($this->fs->path('/dir1'), []);
        $this->assertRemaining(array_keys(self::FILES_AND_DIRECTORIES));
    }

    public function testNotEmptyIgnores(): void
    {
        GlobCleaner::clean($this->fs->path('/dir1'), [
            'file*',
            '!/dir11/file2.xls',
            '/dir12',
        ]);
        GlobCleaner::clean($this->fs->path('/dir2'), [
            '/dir21',
            '/dir22',
            '/dir22/.composer-downloads',
            '/dir22/.composer-downloads/*',
            '/dir22/.composer-downloads/file1.json',
        ]);
        GlobCleaner::clean($this->fs->path('/dir3'), [
            '*',
        ]);
        $this->assertRemaining([
            '/dir1',
            '/dir1/dir11',
            '/dir1/dir11/file2.xls',
            '/dir2',
            '/dir2/dir22',
            '/dir2/dir22/.composer-downloads',
            '/dir2/dir22/.composer-downloads/file1.json',
            '/dir3',
            '/file1.exe',
            '/file2.bat',
        ]);
    }

    private function createFilesAndDirectories(): void
    {
        foreach (self::FILES_AND_DIRECTORIES as $path => $type) {
            if (self::DIR === $type) {
                mkdir($this->fs->path($path));
            } else {
                file_put_contents($this->fs->path($path), md5(rand()));
            }
        }
    }

    private function assertRemaining(array $remainingFilesAndDirs): void
    {
        foreach (self::FILES_AND_DIRECTORIES as $path => $type) {
            $remain = \in_array($path, $remainingFilesAndDirs);
            if (self::DIR === $type) {
                $this->assertSame(is_dir($this->fs->path($path)), $remain, sprintf("Directory '%s' is expected to be %s", $path, $remain ? 'remaining' : 'removed'));
            } else {
                $this->assertSame(is_file($this->fs->path($path)), $remain, sprintf("File '%s' is expected to be %s", $path, $remain ? 'remaining' : 'removed'));
            }
        }
    }
}
