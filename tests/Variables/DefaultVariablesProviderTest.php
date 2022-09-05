<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Tests\Variables;

use LastCall\DownloadsPlugin\Variables\DefaultVariablesProvider;
use PHPUnit\Framework\TestCase;

class DefaultVariablesProviderTest extends TestCase
{
    public function getVariablesTests(): array
    {
        return [
            [['{$id}' => 'foo', '{$version}' => '1.0.1'], ['id' => 'foo', 'version' => '1.0.1']],
            [['{$id}' => 'bar', '{$version}' => ''], ['id' => 'bar']],
        ];
    }

    /**
     * @dataProvider getVariablesTests
     */
    public function testGetAll(array $expectedVariables, array $extraFile)
    {
        $this->assertEquals($expectedVariables, DefaultVariablesProvider::getAll($extraFile));
    }
}
