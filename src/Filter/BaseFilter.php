<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;

abstract class BaseFilter implements FilterInterface
{
    private null|string|array $cache = null;

    public function __construct(
        protected string $subpackageName,
        protected PackageInterface $parent
    ) {
    }

    public function filter(array $extraFile): string|array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        return $this->cache = $this->get($extraFile);
    }

    abstract protected function get(array $extraFile): string|array;

    protected function throwException(string $attribute, string $reason): void
    {
        throw new \UnexpectedValueException(sprintf('Attribute "%s" of extra file "%s" defined in package "%s" %s.', $attribute, $this->subpackageName, $this->parent->getName(), $reason));
    }
}
