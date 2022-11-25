<?php
/**
 * Created by PhpStorm.
 * User: totten
 * Date: 8/21/19
 * Time: 6:31 PM.
 */

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
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
        $package->setDistType($this->getDistType());
        $package->setBinaries($this->getBinaries());

        return $package;
    }

    protected function getTrackingData(): array
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
        return $this->parentPath.'/'.$this->extraFile['path'];
    }

    abstract public function download(Composer $composer, IOInterface $io): void;

    abstract public function getTrackingFile(): string;

    abstract protected function getDistType(): string;

    abstract protected function getBinaries(): array;

    protected function isComposerV2(): bool
    {
        $version = method_exists(Composer::class, 'getVersion') ? Composer::getVersion() : Composer::VERSION;

        return version_compare($version, '2.0.0') >= 0;
    }

    public function install(IOInterface $io): void
    {
        $this->createTrackingFile($io);
        $this->markExecutable($io);
    }

    private function createTrackingFile(IOInterface $io): void
    {
        $package = $this->getSubpackage();
        $io->write(sprintf('<info>Create tracking file for <comment>%s</comment></info>', $package->getName()), true, IOInterface::VERY_VERBOSE);
        $trackingFile = $this->getTrackingFile();
        if (!file_exists(\dirname($trackingFile))) {
            mkdir(\dirname($trackingFile), 0777, true);
        }
        file_put_contents($trackingFile, json_encode(
            $this->getTrackingData(),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
        ));
    }

    private function markExecutable(IOInterface $io): void
    {
        $package = $this->getSubpackage();
        foreach ($package->getBinaries() as $bin) {
            $path = $this->parentPath.\DIRECTORY_SEPARATOR.$bin;
            if (Platform::isWindows() || Platform::isWindowsSubsystemForLinux()) {
                $proxy = $path.'.bat';
                if (file_exists($proxy)) {
                    $io->writeError('    Skipped installation of bin '.$bin.'.bat proxy for package '.$package->getName().': a .bat proxy was already installed');
                } else {
                    $caller = BinaryInstaller::determineBinaryCaller($path);
                    file_put_contents($proxy, '@'.$caller.' "%~dp0'.ProcessExecutor::escape(basename($proxy, '.bat')).'" %*');
                }
            } else {
                chmod($path, 0777 ^ umask());
            }
        }
    }
}
