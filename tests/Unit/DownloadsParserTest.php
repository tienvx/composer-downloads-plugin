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

use Composer\Composer;
use Composer\Package\Package;
use LastCall\DownloadsPlugin\DownloadsParser;
use LastCall\DownloadsPlugin\Handler\BaseHandler;
use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use LastCall\DownloadsPlugin\Handler\PharHandler;
use LastCall\DownloadsPlugin\Handler\RarHandler;
use LastCall\DownloadsPlugin\Handler\TarHandler;
use LastCall\DownloadsPlugin\Handler\XzHandler;
use LastCall\DownloadsPlugin\Handler\ZipHandler;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\TestCase;

class DownloadsParserTest extends TestCase
{
    private function getPackageWithExtraDownloads(array $extra = []): Package
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $package->setExtra([
            'downloads' => $extra,
        ]);

        return $package;
    }

    public function testIgnoresPackagesWithoutExtraDownloads(): void
    {
        $package = new Package('foo', '1.0.0', '1.0.0');
        $this->assertEquals([], $this->parse($package));
    }

    public function testIgnoresPackagesWithDefaultDownloads(): void
    {
        $package = $this->getPackageWithExtraDownloads([
            '*' => ['url' => 'foo', 'path' => 'bar'],
        ]);
        $this->assertEquals([], $this->parse($package));
    }

    public function getExplicitDownloadTypeTests(): array
    {
        return [
            ['zip', ZipHandler::class, 'zip'],
            ['rar', RarHandler::class, 'rar'],
            ['tar', TarHandler::class, 'tar'],
            ['xz', XzHandler::class, 'xz'],
            ['file', FileHandler::class, 'file'],
            ['phar', PharHandler::class, 'file'],
            ['gzip', GzipHandler::class, 'file'],
        ];
    }

    /**
     * @dataProvider getExplicitDownloadTypeTests
     */
    public function testExplicitDownloadType(string $downloadType, string $expectedHandlerClass, string $expectedDistType): void
    {
        $package = $this->getPackageWithExtraDownloads([
            'bar' => ['type' => $downloadType, 'url' => 'foo', 'path' => 'baz'],
        ]);
        $expectSubpackage = new Subpackage($package, 'bar', 'foo', $expectedDistType, 'baz');
        if ('phar' === $downloadType) {
            $expectSubpackage->setBinaries(['baz']);
        }
        $parsed = $this->parse($package);
        $this->assertCount(1, $parsed);
        $this->assertInstanceOf($expectedHandlerClass, $parsed[0]);
        $this->assertEquals($expectSubpackage, $this->getSubpackage($parsed[0]));
    }

    public function getMissingDownloadTypeTests(): array
    {
        return [
            ['foo.zip', ZipHandler::class, 'zip'],
            ['foo.zip?foo', ZipHandler::class, 'zip'],
            ['http://example.com/foo.zip?abc#def', ZipHandler::class, 'zip'],
            ['foo.rar', RarHandler::class, 'rar'],
            ['foo.tar.xz', XzHandler::class, 'xz'],
            ['foo.tar.gz', TarHandler::class, 'tar'],
            ['http://example.com/foo.tar.gz?abc#def', TarHandler::class, 'tar'],
            ['foo.tar.bz2', TarHandler::class, 'tar'],
            ['foo.tgz', TarHandler::class, 'tar'],
            ['foo.tar', TarHandler::class, 'tar'],
            ['foo.gz', GzipHandler::class, 'file'],
            ['foo', FileHandler::class, 'file'],
            ['foo.phar', PharHandler::class, 'file'],
        ];
    }

    /**
     * @dataProvider getMissingDownloadTypeTests
     */
    public function testMissingDownloadType(string $url, string $expectedHandlerClass, string $expectedDistType): void
    {
        $package = $this->getPackageWithExtraDownloads([
            'bar' => ['url' => $url, 'path' => 'bar'],
        ]);
        $parsed = $this->parse($package);
        $this->assertCount(1, $parsed);
        $this->assertInstanceOf($expectedHandlerClass, $parsed[0]);
        $this->assertEquals($expectedDistType, $this->getSubpackage($parsed[0])->getDistType());
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
        $package = $this->getPackageWithExtraDownloads([
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
        $this->parse($package);
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
        $package = $this->getPackageWithExtraDownloads([
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
        $this->parse($package);
    }

    public function getReplacesVariableTests(): array
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
     * @dataProvider getReplacesVariableTests
     */
    public function testReplacesVariables(string $expectedUrl, string $url, array $variables): void
    {
        $package = $this->getPackageWithExtraDownloads([
            'bar' => ['url' => $url, 'path' => 'bar', 'variables' => $variables, 'version' => '1.2.3'],
        ]);
        $version = version_compare(Composer::RUNTIME_API_VERSION, '2.0.0') >= 0 ? 'dev-master' : '9999999-dev';
        $expectSubpackage = new Subpackage($package, 'bar', $expectedUrl, 'zip', 'bar', $version, '1.2.3');
        $actualSubpackage = $this->getSubpackage($this->parse($package)[0]);
        $this->assertEquals($expectSubpackage, $actualSubpackage);
    }

    private function parse(Package $package): array
    {
        return iterator_to_array((new DownloadsParser())->parse($package, '/EXAMPLE'));
    }

    private function getSubpackage(BaseHandler $handler): Subpackage
    {
        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getSubpackage');
        $method->setAccessible(true);

        return $method->invoke($handler);
    }
}
