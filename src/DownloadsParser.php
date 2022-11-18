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
use LastCall\DownloadsPlugin\Handler\ArchiveHandler;
use LastCall\DownloadsPlugin\Handler\BaseHandler;
use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\PharHandler;
use Le\SMPLang\SMPLang;
use Closure;

class DownloadsParser
{

    /**
     * @param \Composer\Package\PackageInterface $package
     *
     * @return BaseHandler[]
     *   Each item is a specification of an extra file, with defaults and variables evaluated.
     */
    public function parse(PackageInterface $package, $basePath)
    {
        $extraFiles = [];
        $extra = $package->getExtra();

        $defaults = isset($extra['downloads']['*']) ? $extra['downloads']['*'] : [];

        if (!empty($extra['downloads'])) {
            foreach ((array) $extra['downloads'] as $id => $extraFile) {
                if ($id === '*') continue;

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

    protected function pickClass(array $extraFile): string
    {
        $types = [
            'archive' => ArchiveHandler::CLASS,
            'file' => FileHandler::CLASS,
            'phar' => PharHandler::CLASS,
        ];
        if (isset($extraFile['type'], $types[$extraFile['type']])) {
            return $types[$extraFile['type']];
        }

        $parts = parse_url($extraFile['url']);
        $filename = pathinfo($parts['path'], PATHINFO_BASENAME);
        if (preg_match('/\.(zip|tar\.gz|tgz)$/', $filename)) {
            return $types['archive'];
        }

        return $types['file'];
    }

    protected function getVariables(array $extraFile): array
    {
        $variables = [
            '{$id}' => $extraFile['id'],
            '{$version}' => $extraFile['version'] ?? '',
        ];
        if (!empty($extraFile['variables'])) {
            $smpl = new SMPLang([
                'strtolower' => Closure::fromCallable('strtolower'),
                'php_uname' => Closure::fromCallable('php_uname'),
                'in_array' => Closure::fromCallable('in_array'),
                'str_contains' => Closure::fromCallable('str_contains'),
                'str_starts_with' => Closure::fromCallable('str_starts_with'),
                'str_ends_with' => Closure::fromCallable('str_ends_with'),
                'matches' => fn (string $pattern, string $subject) => preg_match($pattern, $subject) === 1,
                'PHP_OS' => PHP_OS,
                'PHP_OS_FAMILY' => PHP_OS_FAMILY,
                'PHP_SHLIB_SUFFIX' => PHP_SHLIB_SUFFIX,
            ]);
            foreach ((array) $extraFile['variables'] as $key => $value) {
                if (!preg_match('/^{\$[^}]+}$/', $key)) {
                    throw new \UnexpectedValueException(sprintf('Expected variable key in this format "{$variable-name}", "%s" given.', $key));
                }
                $result = $smpl->evaluate($value);
                if (!is_string($result)) {
                    throw new \UnexpectedValueException(sprintf('Expected the the result of expression "%s" to be a string, "%s" given.', $value, get_debug_type($result)));
                }
                $variables[$key] = $result;
            }
        }

        return $variables;
    }
}
