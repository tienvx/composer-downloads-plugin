<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Composer;
use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Subpackage;
use LastCall\DownloadsPlugin\SubpackageInstaller;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

class SubpackageInstallerTest extends TestCase
{
    private ?FileSystem $fs = null;
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    private Subpackage|MockObject $subpackage;
    private HandlerInterface|MockObject $handler;
    private SubpackageInstaller $installer;
    private string $targetPath = '/project/vendor/test/library/files/file';
    private string $trackingFile = '/project/vendor/test/library/files/.composer-downloads/normal-file-098f6bcd4621d373cade4e832627b4f6.json';
    private string $subpackageName = 'vendor/package-name:normal-file';

    protected function setUp(): void
    {
        $this->fs = new FileSystem(); // Keep virtual file system alive during test
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->subpackage = $this->createMock(Subpackage::class);
        $this->handler = $this->createMock(HandlerInterface::class);
        $this->installer = new SubpackageInstaller($this->subpackage, $this->handler);
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testNotInstalled(): void
    {
        $this->subpackage->expects($this->once())->method('getTargetPath')->willReturn($this->targetPath);
        $this->handler->expects($this->once())->method('getTrackingFile')->willReturn($this->trackingFile);
        $this->subpackage->expects($this->never())->method('getName');
        $this->io->expects($this->never())->method('write');
        $this->assertFalse($this->installer->isInstalled($this->io));
    }

    public function testInstalledAndOverrode(): void
    {
        $this->subpackage->expects($this->once())->method('getTargetPath')->willReturn($this->fs->path($this->targetPath));
        $this->handler->expects($this->once())->method('getTrackingFile')->willReturn($this->fs->path($this->trackingFile));
        $this->fs->createDirectory(\dirname($this->targetPath), true);
        $this->fs->createFile($this->targetPath, 'test');
        $this->subpackage->expects($this->once())->method('getName')->willReturn($this->subpackageName);
        $this->subpackage->expects($this->once())->method('getTargetDir')->willReturn('files');
        $this->io->expects($this->once())->method('write')->with("<info>Extra file <comment>{$this->subpackageName}</comment> has been locally overriden in <comment>files</comment>. To reset it, delete and reinstall.</info>", true);
        $this->assertTrue($this->installer->isInstalled($this->io));
    }

    /**
     * @testWith [[]]
     *           [{"key": "value"}]
     *           [{"checksum": "not-match"}]
     */
    public function testDifferentChecksum(array $meta): void
    {
        $this->subpackage->expects($this->once())->method('getTargetPath')->willReturn($this->fs->path($this->targetPath));
        $this->handler->expects($this->once())->method('getTrackingFile')->willReturn($this->fs->path($this->trackingFile));
        $this->fs->createDirectory(\dirname($this->targetPath), true);
        $this->fs->createFile($this->targetPath, 'test');
        $this->fs->createDirectory(\dirname($this->trackingFile), true);
        $this->fs->createFile($this->trackingFile, json_encode($meta));
        $this->subpackage->expects($this->never())->method('getName');
        $this->io->expects($this->never())->method('write');
        $this->assertFalse($this->installer->isInstalled($this->io));
    }

    public function testInstalled(): void
    {
        $this->subpackage->expects($this->once())->method('getTargetPath')->willReturn($this->fs->path($this->targetPath));
        $this->handler->expects($this->once())->method('getTrackingFile')->willReturn($this->fs->path($this->trackingFile));
        $this->handler->expects($this->once())->method('getChecksum')->willReturn('match');
        $this->fs->createDirectory(\dirname($this->targetPath), true);
        $this->fs->createFile($this->targetPath, 'test');
        $this->fs->createDirectory(\dirname($this->trackingFile), true);
        $this->fs->createFile($this->trackingFile, json_encode(['checksum' => 'match']));
        $this->subpackage->expects($this->once())->method('getName')->willReturn($this->subpackageName);
        $this->io->expects($this->once())->method('write')->with("<info>Skip extra file <comment>{$this->subpackageName}</comment></info>", true, IOInterface::VERY_VERBOSE);
        $this->assertTrue($this->installer->isInstalled($this->io));
    }

    public function testInstall(): void
    {
        $this->handler->expects($this->once())->method('getTrackingFile')->willReturn($this->fs->path($this->trackingFile));
        $this->handler->expects($this->once())->method('getTrackingData')->willReturn([
            'key' => 'value',
        ]);
        $this->handler
            ->expects($this->once())
            ->method('install')
            ->with($this->composer, $this->io)
            ->willReturnCallback(function () {
                $this->fs->createDirectory(\dirname($this->targetPath), true);
                $this->fs->createFile($this->targetPath, 'test');
            });
        $this->subpackage->expects($this->exactly(2))->method('getName')->willReturn($this->subpackageName);
        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                ["<info>Download extra file <comment>{$this->subpackageName}</comment></info>", true, IOInterface::VERBOSE],
                ["<info>Create tracking file for <comment>{$this->subpackageName}</comment></info>", true, IOInterface::VERY_VERBOSE],
            );
        $this->installer->install($this->composer, $this->io);
        $this->assertFileExists($this->fs->path($this->trackingFile));
        $this->assertStringEqualsFile(
            $this->fs->path($this->trackingFile),
            '{'.$this->eol().
            '    "key": "value"'.$this->eol().
            '}'
        );
        $this->assertFileExists($this->fs->path($this->targetPath));
        $this->assertSame('test', file_get_contents($this->fs->path($this->targetPath)));
    }

    private function eol(): string
    {
        return "\n";
    }
}
