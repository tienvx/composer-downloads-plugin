<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;

class UrlFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
        private VariablesFilter $variablesFilter
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): string
    {
        if (!isset($extraFile['url'])) {
            $this->throwException('url', 'is required');
        }

        $url = $extraFile['url'];
        if (!\is_string($url)) {
            $this->throwException('url', sprintf('must be string, "%s" given', get_debug_type($url)));
        }

        $url = strtr($url, $this->variablesFilter->filter($extraFile));
        if (false === filter_var($url, \FILTER_VALIDATE_URL)) {
            $this->throwException('url', 'is invalid url');
        }

        return $url;
    }
}
