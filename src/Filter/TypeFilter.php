<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Types;

class TypeFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        private UrlFilter $urlFilter
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): string
    {
        if (!isset($extraFile['type'])) {
            $url = $this->urlFilter->filter($extraFile);

            return $this->parseType($url);
        }

        $type = $extraFile['type'];
        if (!\is_string($type)) {
            $this->throwException('type', sprintf('must be string, "%s" given', get_debug_type($type)));
        }

        if (!\in_array($type, Types::ALL_TYPES)) {
            $this->throwException('type', 'is not supported');
        }

        return $type;
    }

    private function parseType(string $url): string
    {
        $parts = parse_url($url);
        $filename = pathinfo($parts['path'], \PATHINFO_BASENAME);
        if (preg_match('/\.(tar\.gz|tar\.bz2)$/', $filename)) {
            return 'tar';
        }
        if (preg_match('/\.tar\.xz$/', $filename)) {
            return 'xz';
        }
        $extension = pathinfo($parts['path'], \PATHINFO_EXTENSION);

        return Types::mapExtensionToType($extension);
    }
}
