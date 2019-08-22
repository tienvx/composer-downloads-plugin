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
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;

class ExtraFilesParser
{
    const FAKE_VERSION = 'dev-master';

    /**
     * @param \Composer\Package\PackageInterface $package
     *
     * @return \LastCall\ExtraFiles\ExtraFile[]
     */
    public function parse(PackageInterface $package)
    {
        $extraFiles = [];
        $extra = $package->getExtra();

        $defaults = isset($extra['extra-files']['*']) ? $extra['extra-files']['*'] : [];
        $defaults['ignore'] = isset($defaults['ignore']) ? $defaults['ignore'] : NULL;

        if (!empty($extra['extra-files'])) {
            foreach ((array) $extra['extra-files'] as $id => $extraFile) {
                if ($id === '*') continue;

                $vars = ['{$id}' => $id];
                $extraFile = array_merge($defaults, $extraFile);
                $extraFile['id'] = $id;
                foreach (['url', 'path'] as $prop) {
                    $extraFile[$prop] = strtr($extraFile[$prop], $vars);
                }

                $extraFiles[] = $this->createSubpackage($package, $extraFile);
            }
        }
        
        return $extraFiles;
    }

    /**
     * @param PackageInterface $parent
     * @param array $extraFile
     * @return ExtraFile
     */
    public function createSubpackage(PackageInterface $parent, $extraFile)
    {
        $versionParser = new VersionParser();
        $file = new ExtraFile(
            $parent,
            $extraFile['id'],
            $extraFile['url'],
            $this->parseDistType($extraFile['url']),
            $extraFile['path'],
            $parent instanceof RootPackageInterface ? $versionParser->normalize(self::FAKE_VERSION) : $parent->getVersion(),
            $parent instanceof RootPackageInterface ? self::FAKE_VERSION : $parent->getPrettyVersion(),
            $extraFile['ignore']
        );
        return $file;
    }

    public function parseDistType($url)
    {
        $parts = parse_url($url);
        $filename = pathinfo($parts['path'], PATHINFO_BASENAME);
        if (preg_match('/\.zip$/', $filename)) {
            return 'zip';
        }
        if (preg_match('/\.(tar\.gz|tgz)$/', $filename)) {
            return 'tar';
        }

        return 'file';
    }
}
