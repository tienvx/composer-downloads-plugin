<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Platform;

class PharHandler extends FileHandler
{
    public function download(Composer $composer, IOInterface $io)
    {
        parent::download($composer, $io);

        if (Platform::isWindows()) {
            // TODO make .bat or .cmd
        } else {
            chmod($this->getTargetPath(), 0777 ^ umask());
        }
    }
}
