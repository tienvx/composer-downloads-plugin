<?php

namespace LastCall\DownloadsPlugin\Tests\Unit;

use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Subpackage;
use LastCall\DownloadsPlugin\Types;
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    public function testIsArchiveType(): void
    {
        foreach (Types::ARCHIVE_TYPES as $type) {
            $this->assertTrue(Types::isArchiveType($type));
        }
        foreach (Types::FILE_TYPES as $type) {
            $this->assertFalse(Types::isArchiveType($type));
        }
    }

    public function testIsFileType(): void
    {
        foreach (Types::FILE_TYPES as $type) {
            $this->assertTrue(Types::isFileType($type));
        }
        foreach (Types::ARCHIVE_TYPES as $type) {
            $this->assertFalse(Types::isFileType($type));
        }
    }

    public function testMapExtensionToType(): void
    {
        foreach (Types::EXTENSION_TO_TYPE_MAP as $extension => $type) {
            $this->assertSame($type, Types::mapExtensionToType($extension));
        }
        $this->assertSame('file', Types::mapExtensionToType('mp3'));
    }

    public function testCreateHandler(): void
    {
        foreach (Types::TYPE_TO_HANDLER_CLASS_MAP as $type => $class) {
            $this->assertCreateHandler($class, $type);
        }
        $this->assertCreateHandler(FileHandler::class, 'mp4');
    }

    private function assertCreateHandler(string $class, string $type): void
    {
        $subpackage = $this->createMock(Subpackage::class);
        $subpackage->expects($this->once())->method('getSubpackageType')->willReturn($type);
        $this->assertInstanceOf($class, Types::createHandler($subpackage));
    }

    public function testMapTypeToDistType(): void
    {
        foreach (Types::ALL_TYPES as $type) {
            $this->assertSame(Types::TYPE_PHAR === $type ? 'file' : $type, Types::mapTypeToDistType($type));
        }
    }
}
