<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;

class VersionFilter extends BaseFilter
{
    public const FAKE_VERSION = 'dev-master';

    private VersionParser $versionParser;

    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        ?VersionParser $versionParser = null
    ) {
        parent::__construct($subpackageName, $parent);
        $this->versionParser = $versionParser ?? new VersionParser();
    }

    protected function get(array $extraFile): array
    {
        if (isset($extraFile['version'])) {
            $value = $extraFile['version'];
            if (!\is_string($value)) {
                $this->throwException('version', sprintf('must be string, "%s" given', get_debug_type($value)));
            }

            // $version = $this->versionParser->normalize($version);
            $version = $this->versionParser->normalize(self::FAKE_VERSION);
            $prettyVersion = $value;
        } elseif ($this->parent instanceof RootPackageInterface) {
            $version = $this->versionParser->normalize(self::FAKE_VERSION);
            $prettyVersion = self::FAKE_VERSION;
        } else {
            $version = $this->parent->getVersion();
            $prettyVersion = $this->parent->getPrettyVersion();
        }

        return [$version, $prettyVersion];
    }
}
