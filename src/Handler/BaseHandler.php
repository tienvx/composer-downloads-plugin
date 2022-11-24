<?php
/**
 * Created by PhpStorm.
 * User: totten
 * Date: 8/21/19
 * Time: 6:31 PM.
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

    protected ?Subpackage $subpackage = null;

    public function __construct(protected PackageInterface $parent, protected string $parentPath, protected array $extraFile)
    {
    }

    public function getSubpackage(): Subpackage
    {
        if (null === $this->subpackage) {
            $this->subpackage = $this->createSubpackage();
        }

        return $this->subpackage;
    }

    protected function createSubpackage(): Subpackage
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

    public function createTrackingData(): array
    {
        return [
            'name' => $this->getSubpackage()->getName(),
            'url' => $this->getSubpackage()->getDistUrl(),
            'checksum' => $this->getChecksum(),
        ];
    }

    /**
     * @return string A unique identifier for this configuration of this asset.
     *                If the identifier changes, that implies that the asset should be
     *                replaced/redownloaded.
     */
    public function getChecksum(): string
    {
        $extraFile = $this->extraFile;

        return hash('sha256', serialize([
            static::class,
            $extraFile['id'],
            $extraFile['url'],
            $extraFile['path'],
        ]));
    }

    public function getTargetPath(): string
    {
        return $this->parentPath.'/'.$this->extraFile['path'];
    }

    abstract public function download(Composer $composer, IOInterface $io): void;

    abstract public function getTrackingFile(): string;

    protected function isComposerV2(): bool
    {
        $version = method_exists(Composer::class, 'getVersion') ? Composer::getVersion() : Composer::VERSION;

        return version_compare($version, '2.0.0') >= 0;
    }
}
