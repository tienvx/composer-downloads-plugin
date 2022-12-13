<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Types;
use Symfony\Component\Filesystem\Path;

class ExecutableFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        private string $parentPath,
        private TypeFilter $typeFilter,
        private PathFilter $pathFilter
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): array
    {
        $executable = $extraFile['executable'] ?? null;
        $type = $this->typeFilter->filter($extraFile);
        $path = $this->pathFilter->filter($extraFile);

        if (Types::TYPE_PHAR === $type) {
            if (isset($executable) && true !== $executable) {
                $this->throwException('executable', sprintf('must be true, "%s" given', get_debug_type($executable)));
            } else {
                $executable = [$path];
            }
        }

        if (\in_array($type, [Types::TYPE_FILE, Types::TYPE_GZIP])) {
            if (isset($executable) && !\is_bool($executable)) {
                $this->throwException('executable', sprintf('must be boolean, "%s" given', get_debug_type($executable)));
            } else {
                $executable = $executable ? [$path] : [];
            }
        }

        if (Types::isArchiveType($type)) {
            $executable = $executable ?? [];
            if (!\is_array($executable)) {
                $this->throwException('executable', sprintf('must be array, "%s" given', get_debug_type($executable)));
            }
            if (!empty($executable) && !empty(array_filter($executable, fn (mixed $path) => !$this->isValidPath($path)))) {
                $this->throwException('executable', 'are not valid paths');
            }
        }

        return $executable;
    }

    private function isValidPath(mixed $path): bool
    {
        if (!\is_string($path)) {
            return false;
        }

        if (Path::isAbsolute($path)) {
            return false;
        }

        return Path::isBasePath($this->parentPath, $this->parentPath.\DIRECTORY_SEPARATOR.$path);
    }
}
