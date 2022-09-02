<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Tests\Fixtures;

use LastCall\DownloadsPlugin\Variables\DefaultVariablesProvider;
use LastCall\DownloadsPlugin\Variables\VariablesProviderInterface;

class SystemVariablesProvider extends DefaultVariablesProvider implements VariablesProviderInterface
{
    public static function getAll(array $extraFile): array
    {
        $machineType = php_uname('m');
        $architectureMap = [
            'Windows' => [
                'AMD64' => 'amd64',
            ],
            'Linux' => [
                'x86_64' => 'amd64',
            ],
            'Darwin' => [
                'x86_64' => 'all',
            ],
        ];
        $extensionMap = [
            'Windows' => 'zip',
        ];

        return parent::getAll($extraFile) + [
            '{$os}' => lcfirst(\PHP_OS_FAMILY),
            '{$architecture}' => $architectureMap[\PHP_OS_FAMILY][$machineType] ?? $machineType,
            '{$extension}' => $extensionMap[\PHP_OS_FAMILY] ?? 'tar.gz',
        ];
    }
}
