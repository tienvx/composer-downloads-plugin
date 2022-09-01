<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;
use LastCall\DownloadsPlugin\Subpackage;

abstract class BaseHandler
{
    public const FAKE_VERSION = 'dev-master';
    public const DOT_DIR = '.composer-downloads';

    /**
     * @var array
     *            File specification from composer.json, with defaults/substitutions applied.
     */
    protected $extraFile;

    /**
     * @var PackageInterface
     */
    protected $parent;

    /**
     * @var string
     *             Path to the parent package
     */
    protected $parentPath;

    /**
     * @var Subpackage
     */
    protected $subpackage;

    /**
     * BaseHandler constructor.
     *
     * @param string $parentPath
     * @param array  $extraFile
     */
    public function __construct(PackageInterface $parent, $parentPath, $extraFile)
    {
        $this->parent = $parent;
        $this->parentPath = $parentPath;
        $this->extraFile = $extraFile;
    }

    public function getSubpackage()
    {
        if (null === $this->subpackage) {
            $this->subpackage = $this->createSubpackage();
        }

        return $this->subpackage;
    }

    /**
     * @return Subpackage
     */
    public function createSubpackage()
    {
        $versionParser = new VersionParser();
        $extraFile = $this->extraFile;
        $parent = $this->parent;

        if (isset($extraFile['version'])) {
            // $version = $versionParser->normalize($extraFile['version']);
            $version = $versionParser->normalize(self::FAKE_VERSION);
            $prettyVersion = $extraFile['version'];
        } elseif ($parent instanceof RootPackageInterface) {
            $version = $versionParser->normalize(self::FAKE_VERSION);
            $prettyVersion = self::FAKE_VERSION;
        } else {
            $version = $parent->getVersion();
            $prettyVersion = $parent->getPrettyVersion();
        }

        $package = new Subpackage(
            $parent,
            $extraFile['id'],
            $extraFile['url'],
            null,
            $extraFile['path'],
            $version,
            $prettyVersion
        );

        return $package;
    }

    public function createTrackingData()
    {
        return [
            'name' => $this->getSubpackage()->getName(),
            'url' => $this->getSubpackage()->getDistUrl(),
            'checksum' => $this->getChecksum(),
        ];
    }

    /**
     * @return string
     *                A unique identifier for this configuration of this asset.
     *                If the identifier changes, that implies that the asset should be
     *                replaced/redownloaded.
     */
    public function getChecksum()
    {
        $extraFile = $this->extraFile;

        return hash('sha256', serialize([
            static::class,
            $extraFile['id'],
            $extraFile['url'],
            $extraFile['path'],
        ]));
    }

    /**
     * @return string
     */
    public function getTargetPath()
    {
        return $this->parentPath.'/'.$this->extraFile['path'];
    }

    abstract public function download(Composer $composer, IOInterface $io);

    /**
     * @return string
     */
    abstract public function getTrackingFile();
}
