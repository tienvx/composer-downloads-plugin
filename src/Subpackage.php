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
        PackageInterface $parent,
        private string $parentPath,
        private string $subpackageName,
        private string $subpackageType,
        private array $executable,
        private array $ignore,
        string $url,
        string $path,
        string $version,
        string $prettyVersion,
    ) {
        parent::__construct(
            sprintf('%s:%s', $parent->getName(), $subpackageName),
            $version,
            $prettyVersion
        );
        $this->setDistUrl($url);
        $this->setDistType(Types::mapTypeToDistType($subpackageType));
        $this->setTargetDir($path);
        $this->setInstallationSource('dist');
    }

    public function getParentPath(): string
    {
        return $this->parentPath;
    }

    public function getSubpackageName(): string
    {
        return $this->subpackageName;
    }

    public function getExecutable(): array
    {
        return $this->executable;
    }

    public function getIgnore(): array
    {
        return $this->ignore;
    }

    public function getTargetPath(): string
    {
        return $this->parentPath.\DIRECTORY_SEPARATOR.$this->getTargetDir();
    }

    public function getSubpackageType(): string
    {
        return $this->subpackageType;
    }
}
