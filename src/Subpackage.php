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

use Composer\Package\Package;
use Composer\Package\PackageInterface;

/**
 * Class Subpackage.
 */
class Subpackage extends Package
{
    public function __construct(
        private PackageInterface $parent,
        string $name,
        ?string $url,
        ?string $type,
        ?string $path,
        ?string $version = null,
        ?string $prettyVersion = null
    ) {
        parent::__construct(
            sprintf('%s:%s', $parent->getName(), $name),
            $version ?: $parent->getVersion(),
            $prettyVersion ?: $parent->getPrettyVersion()
        );
        $this->setDistUrl($url);
        $this->setDistType($type);
        $this->setTargetDir($path);
        $this->setInstallationSource('dist');
    }
}
