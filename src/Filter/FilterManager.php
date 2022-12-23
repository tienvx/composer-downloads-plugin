<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Exception\OutOfRangeException;

class FilterManager
{
    public const PATH = 'path';
    public const URL = 'url';
    public const VARIABLES = 'variables';
    public const TYPE = 'type';
    public const VERSION = 'version';
    public const EXECUTABLE = 'executable';
    public const IGNORE = 'ignore';

    private array $filters;

    public function __construct(
        private string $subpackageName,
        private PackageInterface $parent,
        private string $parentPath
    ) {
        $this->filters = [
            self::VARIABLES => $variablesFilter = new VariablesFilter($subpackageName, $parent),
            self::PATH => $pathFilter = new PathFilter($subpackageName, $parent, $parentPath, $variablesFilter),
            self::URL => $urlFilter = new UrlFilter($subpackageName, $parent, $variablesFilter),
            self::VERSION => new VersionFilter($subpackageName, $parent),
            self::TYPE => $typeFilter = new TypeFilter($subpackageName, $parent, $urlFilter),
            self::EXECUTABLE => new ExecutableFilter($subpackageName, $parent, $parentPath, $typeFilter, $pathFilter),
            self::IGNORE => new IgnoreFilter($subpackageName, $parent, $typeFilter, $variablesFilter),
        ];
    }

    public function get(string $name): FilterInterface
    {
        if (!isset($this->filters[$name])) {
            throw new OutOfRangeException(sprintf('Filter "%s" not found.', $name));
        }

        return $this->filters[$name];
    }
}
