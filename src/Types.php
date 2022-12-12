<?php

namespace LastCall\DownloadsPlugin;

use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Handler\PharHandler;
use LastCall\DownloadsPlugin\Handler\RarHandler;
use LastCall\DownloadsPlugin\Handler\TarHandler;
use LastCall\DownloadsPlugin\Handler\XzHandler;
use LastCall\DownloadsPlugin\Handler\ZipHandler;

class Types
{
    public const TYPE_ZIP = 'zip';
    public const TYPE_RAR = 'rar';
    public const TYPE_TAR = 'tar';
    public const TYPE_XZ = 'xz';
    public const TYPE_FILE = 'file';
    public const TYPE_PHAR = 'phar';
    public const TYPE_GZIP = 'gzip';

    public const ARCHIVE_TYPES = [
        self::TYPE_ZIP,
        self::TYPE_RAR,
        self::TYPE_TAR,
        self::TYPE_XZ,
    ];
    public const FILE_TYPES = [
        self::TYPE_FILE,
        self::TYPE_PHAR,
        self::TYPE_GZIP,
    ];
    public const ALL_TYPES = [
        self::TYPE_ZIP,
        self::TYPE_RAR,
        self::TYPE_TAR,
        self::TYPE_XZ,
        self::TYPE_FILE,
        self::TYPE_PHAR,
        self::TYPE_GZIP,
    ];
    public const EXTENSION_TO_TYPE_MAP = [
        'zip' => self::TYPE_ZIP,
        'rar' => self::TYPE_RAR,
        'tgz' => self::TYPE_TAR,
        'tar' => self::TYPE_TAR,
        'gz' => self::TYPE_GZIP,
        'phar' => self::TYPE_PHAR,
    ];
    public const TYPE_TO_HANDLER_CLASS_MAP = [
        self::TYPE_ZIP => ZipHandler::class,
        self::TYPE_RAR => RarHandler::class,
        self::TYPE_TAR => TarHandler::class,
        self::TYPE_XZ => XzHandler::class,
        self::TYPE_FILE => FileHandler::class,
        self::TYPE_PHAR => PharHandler::class,
        self::TYPE_GZIP => GzipHandler::class,
    ];

    public static function isArchiveType(string $type): bool
    {
        return \in_array($type, self::ARCHIVE_TYPES);
    }

    public static function isFileType(string $type): bool
    {
        return \in_array($type, self::FILE_TYPES);
    }

    public static function mapExtensionToType(string $extension): string
    {
        return self::EXTENSION_TO_TYPE_MAP[$extension] ?? self::TYPE_FILE;
    }

    public static function createHandler(Subpackage $subpackage): HandlerInterface
    {
        $class = self::TYPE_TO_HANDLER_CLASS_MAP[$subpackage->getSubpackageType()] ?? FileHandler::class;

        return new $class($subpackage);
    }

    public static function mapTypeToDistType(string $type): string
    {
        return self::TYPE_PHAR === $type ? 'file' : $type;
    }
}
