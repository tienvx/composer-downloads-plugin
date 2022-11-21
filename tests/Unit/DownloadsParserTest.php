<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin\Tests\Unit;

use Composer\Package\Package;
use LastCall\DownloadsPlugin\DownloadsParser;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\TestCase;

class DownloadsParserTest extends TestCase
{
    private function getPackage(array $extra = []): Package
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $package->setExtra([
            'downloads' => $extra,
        ]);

        return $package;
    }

    public function testIgnoresPackagesWithoutDownloads(): void
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $parser = new DownloadsParser();
        $this->assertEquals([], $parser->parse($package, '/EXAMPLE'));
    }

    public function testAddsFiles(): void
    {
        $package = $this->getPackage([
            'bar' => ['url' => 'foo', 'path' => 'bar'],
        ]);
        $expectSubpackage = new Subpackage($package, 'bar', 'foo', 'file', 'bar');
        $actualSubpackage = (new DownloadsParser())->parse($package, '/EXAMPLE')[0]->getSubpackage();
        $this->assertEquals([$expectSubpackage], [$actualSubpackage]);
    }

    public function getDownloadTypeTests(): array
    {
        return [
            ['zip', 'foo.zip'],
            ['zip', 'foo.zip?foo'],
            ['zip', 'http://example.com/foo.zip?abc#def'],
            ['rar', 'foo.rar'],
            ['xz', 'foo.tar.xz'],
            ['tar', 'foo.tar.gz'],
            ['tar', 'http://example.com/foo.tar.gz?abc#def'],
            ['tar', 'foo.tar.bz2'],
            ['tar', 'foo.tgz'],
            ['tar', 'foo.tar'],
            ['gzip', 'foo.gz'],
            ['file', 'foo'],
        ];
    }

    /**
     * @dataProvider getDownloadTypeTests
     */
    public function testSetsDownloadType(string $expectedType, string $url): void
    {
        $package = $this->getPackage([
            'bar' => ['url' => $url, 'path' => 'bar'],
        ]);
        $parsed = (new DownloadsParser())->parse($package, '/EXAMPLE');
        $this->assertEquals($expectedType, $parsed[0]->getSubpackage()->getDistType());
    }

    public function getInvalidVariableKeyTests(): array
    {
        return [
            ['baz'],
            ['$baz'],
            ['{baz}'],
            ['${baz}'],
            ['{$baz'],
            ['$baz}'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableKeyTests
     */
    public function testInvalidVariableKey(string $invalidVariableKey): void
    {
        $package = $this->getPackage([
            'bar' => [
                'url' => "http://example.com/foo-$invalidVariableKey.zip",
                'path' => 'bar',
                'variables' => [
                    $invalidVariableKey => '"baz"',
                ],
            ],
        ]);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectDeprecationMessage(sprintf('Expected variable key in this format "{$variable-name}", "%s" given.', $invalidVariableKey));
        (new DownloadsParser())->parse($package, '/EXAMPLE');
    }

    public function getInvalidVariableValueTests(): array
    {
        return [
            ["{ foo: 'bar' }", 'stdClass'],
            ["['foo', 'baz']", 'array'],
            ['true', 'bool'],
            ['false', 'bool'],
            ['null', 'null'],
            ['123', 'int'],
            ['1.92', 'float'],
            ['1e-2', 'float'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableValueTests
     */
    public function testInvalidVariableValue(string $invalidVariableValue, string $type): void
    {
        $package = $this->getPackage([
            'bar' => [
                'url' => 'http://example.com/foo-{$baz}.zip',
                'path' => 'bar',
                'variables' => [
                    '{$baz}' => $invalidVariableValue,
                ],
            ],
        ]);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectDeprecationMessage(sprintf('Expected the the result of expression "%s" to be a string, "%s" given.', $invalidVariableValue, $type));
        (new DownloadsParser())->parse($package, '/EXAMPLE');
    }

    public function getVariableTests(): array
    {
        return [
            ['http://example.com/foo.zip', 'http://example.com/foo.zip', ['{$foo}' => '"foo"']],
            ['http://example.com/{$bar}.zip', 'http://example.com/{$bar}.zip', ['{$foo}' => '"foo"']],
            ['http://example.com/foo.zip', 'http://example.com/{$foo}.zip', ['{$foo}' => '"foo"']],
            ['http://example.com/foo-bar-1.2.3.zip', 'http://example.com/{$foo}-{$id}-{$version}.zip', ['{$foo}' => '"foo"']],
            ['http://example.com/foo-{$bar}.zip', 'http://example.com/{$foo}-{$bar}.zip', ['{$foo}' => '"foo"']],
            ['http://example.com/foo-bar-baz.zip', 'http://example.com/{$foo}-{$bar}.zip', ['{$foo}' => '"foo"', '{$bar}' => '"bar"~"-"~"baz"']],
        ];
    }

    /**
     * @dataProvider getVariableTests
     */
    public function testReplacesVariables(string $expectedUrl, string $url, array $variables): void
    {
        $package = $this->getPackage([
            'bar' => ['url' => $url, 'path' => 'bar', 'variables' => $variables, 'version' => '1.2.3'],
        ]);
        $expectSubpackage = new Subpackage($package, 'bar', $expectedUrl, 'zip', 'bar', 'dev-master', '1.2.3');
        $actualSubpackage = (new DownloadsParser())->parse($package, '/EXAMPLE')[0]->getSubpackage();
        $this->assertEquals($expectSubpackage, $actualSubpackage);
    }
}
