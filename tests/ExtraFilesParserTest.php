<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ExtraFiles\Tests;

use Composer\Package\Package;
use LastCall\ExtraFiles\Subpackage;
use LastCall\ExtraFiles\ExtraFilesParser;
use PHPUnit\Framework\TestCase;

class ExtraFilesParserTest extends TestCase
{
    private function getPackage(array $extra = [])
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $package->setExtra([
            'extra-files' => $extra,
        ]);

        return $package;
    }

    public function testIgnoresPackagesWithoutExtraFiles()
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $parser = new ExtraFilesParser();
        $this->assertEquals([], $parser->parse($package));
    }

    public function testAddsFiles()
    {
        $package = $this->getPackage([
            'bar' => ['url' => 'foo', 'path' => 'bar'],
        ]);
        $this->assertEquals(
            [new Subpackage($package, 'bar', 'foo', 'file', 'bar')],
            [(new ExtraFilesParser())->parse($package)[0]->getSubpackage()]
        );
    }

    public function getDownloadTypeTests()
    {
        return [
            ['zip', 'foo.zip'],
            ['zip', 'foo.zip?foo'],
            ['zip', 'http://example.com/foo.zip?abc#def'],
            ['tar', 'foo.tar.gz'],
            ['tar', 'http://example.com/foo.tar.gz?abc#def'],
            ['tar', 'foo.tgz'],
            ['file', 'foo'],
        ];
    }

    /**
     * @dataProvider getDownloadTypeTests
     */
    public function testSetsDownloadType($expectedType, $url)
    {
        $package = $this->getPackage([
            'bar' => ['url' => $url, 'path' => 'bar'],
        ]);
        $parsed = (new ExtraFilesParser())->parse($package);
        $this->assertEquals($expectedType, $parsed[0]->getSubpackage()->getDistType());
    }
}
