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
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Subpackage;

abstract class BaseHandler implements HandlerInterface
{
    public const DOT_DIR = '.composer-downloads';

    private BinariesInstaller $binariesInstaller;

    public function __construct(
        protected Subpackage $subpackage,
        ?BinariesInstaller $binariesInstaller = null
    ) {
        $this->binariesInstaller = $binariesInstaller ?? new BinariesInstaller();
    }

    public function getSubpackage(): Subpackage
    {
        return $this->subpackage;
    }

    public function getTrackingData(): array
    {
        return [
            'name' => $this->subpackage->getName(),
            'url' => $this->subpackage->getDistUrl(),
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
            'id' => $this->subpackage->getSubpackageName(),
            'url' => $this->subpackage->getDistUrl(),
            'path' => $this->subpackage->getTargetDir(),
            'executable' => $this->subpackage->getExecutable(),
        ];
    }

    protected function isComposerV2(): bool
    {
        return version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0;
    }

    public function install(Composer $composer, IOInterface $io): void
    {
        $this->download($composer, $io);
        $this->binariesInstaller->install($this->subpackage, $io);
    }

    abstract protected function download(Composer $composer, IOInterface $io): void;
}
