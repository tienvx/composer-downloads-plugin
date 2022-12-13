<?php

namespace LastCall\DownloadsPlugin;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Filter\FilterManager;

class SubpackageFactory
{
    public function __construct(private ?FilterManager $filterManager = null)
    {
    }

    private function createSingle(
        string $subpackageName,
        array $extraFile,
        PackageInterface $parent,
        string $parentPath
    ): Subpackage {
        $filterManager = $this->filterManager ?? new FilterManager($subpackageName, $parent, $parentPath);

        $executable = $filterManager->get(FilterManager::EXECUTABLE)->filter($extraFile);
        $ignore = $filterManager->get(FilterManager::IGNORE)->filter($extraFile);
        $url = $filterManager->get(FilterManager::URL)->filter($extraFile);
        $path = $filterManager->get(FilterManager::PATH)->filter($extraFile);
        [$version, $prettyVersion] = $filterManager->get(FilterManager::VERSION)->filter($extraFile);
        $type = $filterManager->get(FilterManager::TYPE)->filter($extraFile);

        return new Subpackage(
            $parent,
            $parentPath,
            $subpackageName,
            $type,
            $executable,
            $ignore,
            $url,
            $path,
            $version,
            $prettyVersion
        );
    }

    /**
     * @return Subpackage[]
     */
    public function create(PackageInterface $package, string $basePath): array
    {
        $subpackages = [];
        $extra = $package->getExtra();
        $defaults = $extra['downloads']['*'] ?? [];

        if (!empty($extra['downloads'])) {
            foreach ((array) $extra['downloads'] as $id => $extraFile) {
                if ('*' === $id) {
                    continue;
                }

                $subpackages[] = $this->createSingle($id, array_merge($defaults, $extraFile), $package, $basePath);
            }
        }

        return $subpackages;
    }
}
