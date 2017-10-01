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

use Composer\Package\Package;
use Composer\Package\PackageInterface;

class ExtraFile extends Package
{
    private $parent;

    public function __construct(PackageInterface $parent, $id, $url, $type, $path)
    {
        $this->parent = $parent;
        $this->id = $id;
        $this->setDistUrl($url);
        $this->setDistType($type);
        $this->setTargetDir($path);
        $this->setInstallationSource('dist');
    }

    public function getName()
    {
        return sprintf('%s:%s', $this->parent->getName(), $this->id);
    }
}
