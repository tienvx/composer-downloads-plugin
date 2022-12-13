<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Types;

class IgnoreFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        private TypeFilter $typeFilter
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): array
    {
        if (!Types::isArchiveType($this->typeFilter->filter($extraFile)) || !isset($extraFile['ignore'])) {
            return [];
        }

        $ignore = $extraFile['ignore'];
        if (!\is_array($ignore)) {
            $this->throwException('ignore', sprintf('must be array, "%s" given', get_debug_type($ignore)));
        }

        return $ignore;
    }
}
