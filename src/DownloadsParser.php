<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Handler\BaseHandler;
use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use LastCall\DownloadsPlugin\Handler\PharHandler;
use LastCall\DownloadsPlugin\Handler\RarHandler;
use LastCall\DownloadsPlugin\Handler\TarHandler;
use LastCall\DownloadsPlugin\Handler\XzHandler;
use LastCall\DownloadsPlugin\Handler\ZipHandler;
use Le\SMPLang\SMPLang;

class DownloadsParser
{
    public const EXTENSION_TO_TYPE_MAP = [
        'zip' => 'zip',
        'rar' => 'rar',
        'tgz' => 'tar',
        'tar' => 'tar',
        'gz' => 'gzip',
        'phar' => 'phar',
    ];
    public const TYPE_TO_HANDLER_CLASS_MAP = [
        'zip' => ZipHandler::class,
        'rar' => RarHandler::class,
        'tar' => TarHandler::class,
        'xz' => XzHandler::class,
        'file' => FileHandler::class,
        'phar' => PharHandler::class,
        'gzip' => GzipHandler::class,
    ];

    /**
     * @return baseHandler[] Each item is a specification of an extra file, with defaults and variables evaluated
     */
    public function parse(PackageInterface $package, string $basePath): array
    {
        $extraFiles = [];
        $extra = $package->getExtra();

        $defaults = $extra['downloads']['*'] ?? [];

        if (!empty($extra['downloads'])) {
            foreach ((array) $extra['downloads'] as $id => $extraFile) {
                if ('*' === $id) {
                    continue;
                }

                $extraFile = array_merge($defaults, $extraFile);
                $extraFile['id'] = $id;
                foreach (['url', 'path'] as $prop) {
                    if (isset($extraFile[$prop])) {
                        $extraFile[$prop] = strtr($extraFile[$prop], $this->getVariables($extraFile));
                    }
                }

                $class = $this->pickClass($extraFile);
                $extraFiles[] = new $class($package, $basePath, $extraFile);
            }
        }

        return $extraFiles;
    }

    private function pickClass(array $extraFile): string
    {
        if (isset($extraFile['type'], self::TYPE_TO_HANDLER_CLASS_MAP[$extraFile['type']])) {
            return self::TYPE_TO_HANDLER_CLASS_MAP[$extraFile['type']];
        }

        return self::TYPE_TO_HANDLER_CLASS_MAP[$this->parseType($extraFile['url'])];
    }

    private function parseType(string $url): string
    {
        $parts = parse_url($url);
        $filename = pathinfo($parts['path'], \PATHINFO_BASENAME);
        if (preg_match('/\.(tar\.gz|tar\.bz2)$/', $filename)) {
            return 'tar';
        }
        if (preg_match('/\.tar\.xz$/', $filename)) {
            return 'xz';
        }
        $extension = pathinfo($parts['path'], \PATHINFO_EXTENSION);

        return self::EXTENSION_TO_TYPE_MAP[$extension] ?? 'file';
    }

    private function getVariables(array $extraFile): array
    {
        $variables = [
            '{$id}' => $extraFile['id'],
            '{$version}' => $extraFile['version'] ?? '',
        ];
        if (!empty($extraFile['variables'])) {
            $smpl = new SMPLang([
                'range' => \Closure::fromCallable('range'),
                'strtolower' => \Closure::fromCallable('strtolower'),
                'php_uname' => \Closure::fromCallable('php_uname'),
                'in_array' => \Closure::fromCallable('in_array'),
                'str_contains' => \Closure::fromCallable('str_contains'),
                'str_starts_with' => \Closure::fromCallable('str_starts_with'),
                'str_ends_with' => \Closure::fromCallable('str_ends_with'),
                'matches' => fn (string $pattern, string $subject) => 1 === preg_match($pattern, $subject),
                'PHP_OS' => \PHP_OS,
                'PHP_OS_FAMILY' => \PHP_OS_FAMILY,
                'PHP_SHLIB_SUFFIX' => \PHP_SHLIB_SUFFIX,
                'DIRECTORY_SEPARATOR' => \DIRECTORY_SEPARATOR,
            ]);
            foreach ((array) $extraFile['variables'] as $key => $value) {
                if (!preg_match('/^{\$[^}]+}$/', $key)) {
                    throw new \UnexpectedValueException(sprintf('Expected variable key in this format "{$variable-name}", "%s" given.', $key));
                }
                $result = $smpl->evaluate($value);
                if (!\is_string($result)) {
                    throw new \UnexpectedValueException(sprintf('Expected the the result of expression "%s" to be a string, "%s" given.', $value, get_debug_type($result)));
                }
                $variables[$key] = $result;
            }
        }

        return $variables;
    }
}
