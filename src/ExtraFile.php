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

use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;

class ExtraFile extends Package
{
    private $parent;

    protected $ignore;

    public function __construct(PackageInterface $parent, $id, $url, $type, $path, $version = NULL, $prettyVersion = NULL, $ignore = NULL)
    {

        parent::__construct(
            sprintf('%s:%s', $parent->getName(), $id),
            $version ? $version : $parent->getVersion(),
            $prettyVersion ? $prettyVersion : $parent->getPrettyVersion()
        );
        $this->parent = $parent;
        $this->id = $id;
        $this->setDistUrl($url);
        $this->setDistType($type);
        $this->setTargetDir($path);
        $this->setInstallationSource('dist');
        $this->ignore = $ignore;
    }

    /**
     * @return string[]|NULL
     *   List of files to exclude. Use '**' to match subdirectories.
     *   Ex: ['.gitignore', '*.md']
     */
    public function findIgnores($targetPath)
    {
        return $this->ignore;
    }

}
