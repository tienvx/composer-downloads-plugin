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

class ExtraFilesParser
{
    /**
     * @param \Composer\Package\PackageInterface $package
     *
     * @return \LastCall\ExtraFiles\ExtraFile[]
     */
    public function parse(PackageInterface $package)
    {
        $extraFiles = [];
        $extra = $package->getExtra();
        if (!empty($extra['extra-files'])) {
            foreach ((array) $extra['extra-files'] as $id => $extraFile) {
                $file = new ExtraFile(
                    $package,
                    $id,
                    $extraFile['url'],
                    $this->parseDistType($extraFile['url']),
                    $extraFile['path']
                );
                $extraFiles[] = $file;
            }
        }

        return $extraFiles;
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
