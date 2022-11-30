<?php

namespace LastCall\DownloadsPlugin;

use LastCall\DownloadsPlugin\Handler\BaseHandler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Gitignore;

class GlobCleaner
{
    public function clean(string $baseDir, array $ignores): void
    {
        if (empty($ignores)) {
            return;
        }

        $dirs = [];
        $finder = new Finder();
        $finder->in($baseDir)->notName(BaseHandler::DOT_DIR)->path(Gitignore::toRegex(implode(\PHP_EOL, $ignores)));
        foreach (iterator_to_array($finder) as $item) {
            if ($item->isDir()) {
                $dirs[] = $item->getPathname();
            } else {
                unlink($item->getPathname());
            }
        }
        foreach ($dirs as $dir) {
            @rmdir($dir);
        }
    }
}
