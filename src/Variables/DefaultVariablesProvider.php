<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Variables;

class DefaultVariablesProvider implements VariablesProviderInterface
{
    public static function getAll(array $extraFile): array
    {
        return [
            '{$id}' => $extraFile['id'],
            '{$version}' => $extraFile['version'] ?? '',
        ];
    }
}
