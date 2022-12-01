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
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Subpackage;

abstract class BaseHandler implements HandlerInterface
{
    public const FAKE_VERSION = 'dev-master';
    public const DOT_DIR = '.composer-downloads';

    private ?Subpackage $subpackage = null;
    private BinariesInstaller $binariesInstaller;

    public function __construct(
        protected PackageInterface $parent,
        protected string $parentPath,
        protected array $extraFile,
        ?BinariesInstaller $binariesInstaller = null
    ) {
        $this->binariesInstaller = $binariesInstaller ?? new BinariesInstaller();
    }

    public function getSubpackage(): Subpackage
    {
        if (null === $this->subpackage) {
            $this->subpackage = $this->createSubpackage();
        }

        return $this->subpackage;
    }

    private function createSubpackage(): Subpackage
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
            $this->getDistType(),
            $extraFile['path'],
            $version,
            $prettyVersion
        );
        $package->setBinaries($this->getBinaries());

        return $package;
    }

    public function getTrackingData(): array
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
        return hash('sha256', serialize($this->getChecksumData()));
    }

    protected function getChecksumData(): array
    {
        return [
            'class' => static::class,
            'id' => $this->extraFile['id'],
            'url' => $this->extraFile['url'],
            'path' => $this->extraFile['path'],
        ];
    }

    public function getTargetPath(): string
    {
        return $this->parentPath.\DIRECTORY_SEPARATOR.$this->extraFile['path'];
    }

    abstract protected function getDistType(): string;

    abstract protected function getBinaries(): array;

    protected function isComposerV2(): bool
    {
        return version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0;
    }

    public function install(Composer $composer, IOInterface $io): void
    {
        $this->download($composer, $io);
        $this->binariesInstaller->install($this->getSubpackage(), $this->parentPath, $io);
    }

    abstract protected function download(Composer $composer, IOInterface $io): void;
}
