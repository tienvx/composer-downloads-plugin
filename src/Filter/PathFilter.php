<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use Symfony\Component\Filesystem\Path;

class PathFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        private string $parentPath,
        private VariablesFilter $variablesFilter
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): string
    {
        if (!isset($extraFile['path'])) {
            $this->throwException('path', 'is required');
        }

        $path = $extraFile['path'];
        if (!\is_string($path)) {
            $this->throwException('path', sprintf('must be string, "%s" given', get_debug_type($path)));
        }

        $path = strtr($path, $this->variablesFilter->filter($extraFile));
        if (Path::isAbsolute($path)) {
            $this->throwException('path', 'must be relative path');
        }

        if (!Path::isBasePath($this->parentPath, $this->parentPath.\DIRECTORY_SEPARATOR.$path)) {
            $this->throwException('path', "must be inside relative to parent package's path");
        }

        return $path;
    }
}
