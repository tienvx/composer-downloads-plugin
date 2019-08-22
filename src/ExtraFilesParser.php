<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ExtraFiles;

use Composer\Package\PackageInterface;
use LastCall\ExtraFiles\Handler\ArchiveHandler;
use LastCall\ExtraFiles\Handler\BaseHandler;
use LastCall\ExtraFiles\Handler\FileHandler;
use LastCall\ExtraFiles\Handler\PharHandler;

class ExtraFilesParser
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

                $vars = ['{$id}' => $id];
                $extraFile = array_merge($defaults, $extraFile);
                $extraFile['id'] = $id;
                foreach (['url', 'path'] as $prop) {
                    if (isset($extraFile[$prop])) {
                        $extraFile[$prop] = strtr($extraFile[$prop], $vars);
                    }
                }

                $class = $this->pickClass($extraFile);
                $extraFiles[] = new $class($package, $basePath, $extraFile);
            }
        }
        
        return $extraFiles;
    }

    public function pickClass($extraFile)
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
}
