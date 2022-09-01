<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin;

use Composer\IO\IOInterface;

class GlobCleaner
{
    /**
     * @param string   $baseDir
     * @param string[] $ignores
     *
     * @return \Generator|void
     */
    public static function clean(IOInterface $io, $baseDir, $ignores)
    {
        if (empty($ignores)) {
            return;
        }

        $dirs = [];

        $finder = new \TOGoS_GitIgnore_FileFinder([
            'ruleset' => \TOGoS_GitIgnore_Ruleset::loadFromStrings($ignores),
            'invertRulesetResult' => false,
            'defaultResult' => false,
            'includeDirectories' => false,
            'callback' => function ($file, $match) use ($baseDir, &$dirs) {
                if ($match) {
                    unlink("$baseDir/$file");
                    $dir = \dirname($file);
                    if ('.' !== $dir) {
                        $dirs[\dirname($file)] = 1;
                    }
                }
            },
        ]);
        $finder->findFiles($baseDir);

        // Cleanup any empy directories
        $dirNames = array_keys($dirs);
        $byLength = function ($a, $b) {
            return \strlen($b) - \strlen($a);
        };
        usort($dirNames, $byLength);

        while ($dirName = array_shift($dirNames)) {
            if (!glob("$baseDir/$dirName/*")) {
                @rmdir("$baseDir/$dirName");
                $dirNames[] = \dirname($dirName);
                usort($dirNames, $byLength);
            }
        }
    }
}
