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

        $defaults = isset($extra['extra-files']['*']) ? $extra['extra-files']['*'] : [];

        if (!empty($extra['extra-files'])) {
            foreach ((array) $extra['extra-files'] as $id => $extraFile) {
                if ($id === '*') continue;

                $vars = ['{$id}' => $id];
                $extraFile = array_merge($defaults, $extraFile);
                $extraFile['id'] = $id;
                foreach (['url', 'path'] as $prop) {
                    if (isset($extraFile[$prop])) {
                        $extraFile[$prop] = strtr($extraFile[$prop], $vars);
                    }
                }

                $class = $this->pickClass($extraFile['url']);
                $extraFiles[] = new $class($package, $basePath, $extraFile);
            }
        }
        
        return $extraFiles;
    }

    public function pickClass($url)
    {
        $parts = parse_url($url);
        $filename = pathinfo($parts['path'], PATHINFO_BASENAME);
        if (preg_match('/\.(zip|tar\.gz|tgz)$/', $filename)) {
            return ArchiveHandler::CLASS;
        }

        return FileHandler::CLASS;
    }
}
