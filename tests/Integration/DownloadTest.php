<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DownloadTest extends TestCase
{
    private static ?string $origDir;
    private static ?string $testDir;
    private static ?Process $server;

    private static function getComposerJson(): array
    {
        return [
            'name' => 'test/download-test',
            'repositories' => [
                'composer-downloads-plugin' => [
                    'type' => 'path',
                    'url' => self::getPluginSourceDir(),
                ],
            ],
            'require' => [
                'tienvx/composer-downloads-plugin' => '@dev',
            ],
            'minimum-stability' => 'dev',
            'extra' => [
                'downloads' => [
                    '*' => [
                        'type' => 'archive',
                        'path' => 'files/{$id}',
                    ],
                    'file' => [
                        'type' => 'file',
                        'url' => 'http://localhost:8000/file/ipsum',
                        'path' => 'files/file/ipsum',
                    ],
                    'phar' => [
                        'type' => 'phar',
                        'url' => 'http://localhost:8000/phar/hello.phar',
                        'path' => 'files/phar/hello',
                    ],
                    'doc' => [
                        'version' => 'v1.2.3',
                        'path' => 'files/doc/{$version}',
                        'variables' => [
                            '{$extension}' => 'PHP_OS_FAMILY === "Windows" ? "zip" : "tar.gz"',
                        ],
                        'url' => 'http://localhost:8000/archive/doc/{$version}/doc.{$extension}',
                    ],
                    'updated-doc' => [
                        'version' => 'v1.3.0',
                        'path' => 'files/doc/{$version}',
                        'url' => 'http://localhost:8000/archive/doc/{$version}/doc.tgz',
                        'ignore' => ['empty.txt'],
                    ],
                    'spreadsheet' => [
                        'url' => 'http://localhost:8000/archive/{$id}.tar.xz',
                    ],
                    'presentation' => [
                        'url' => 'http://localhost:8000/archive/presentation.tar.bz2',
                    ],
                    'text' => [
                        'url' => 'http://localhost:8000/archive/text.tar',
                    ],
                    'image' => [
                        'url' => 'http://localhost:8000/archive/image.rar',
                    ],
                    'xml' => [
                        'type' => 'gzip',
                        'url' => 'http://localhost:8000/archive/empty.xml.gz',
                        'path' => 'files/markup/empty.xml',
                    ],
                    'html' => [
                        'type' => 'gzip',
                        'url' => 'http://localhost:8000/archive/empty.html.gz',
                        'path' => 'files/markup/empty.html',
                    ],
                ],
            ],
            'config' => [
                'allow-plugins' => [
                    'tienvx/composer-downloads-plugin' => true,
                ],
                'secure-http' => false,
            ],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        self::startLocalServer();
        self::initTestProject();
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanTestProjectDir();
        self::stopLocalServer();
    }

    protected function setUp(): void
    {
        self::cleanDir(self::$testDir.\DIRECTORY_SEPARATOR.'files');
    }

    public function getFileChecksums(): array
    {
        return [
            'files/phar/hello' => '047d6ea435e107c214073c58794efc2e6ca1ec8ebcf9b68de21735e5460224c5',
            'files/file/ipsum' => 'dd14cbbf0e74909aac7f248a85d190afd8da98265cef95fc90dfddabea7c2e66',
            'files/doc/v1.2.3/empty.doc' => '60b5e45db3b51c38a5b762e771ee2f19692f52186c42c3930d56bbdf04d21f4e',
            'files/doc/v1.2.3/empty.docx' => '61cdb4b8b9067ab1f4eaa5ba782007c81bdd04283a228b5076aeeb4c9362020b',
            'files/doc/v1.3.0/empty.doc' => '60b5e45db3b51c38a5b762e771ee2f19692f52186c42c3930d56bbdf04d21f4e',
            'files/doc/v1.3.0/empty.docx' => '61cdb4b8b9067ab1f4eaa5ba782007c81bdd04283a228b5076aeeb4c9362020b',
            'files/doc/v1.3.0/empty.pdf' => 'c9a7a7f01b8909dbc5405a6c37372610b853bc76464f167e964c217d8ffdcc3c',
            'files/doc/v1.3.0/empty.txt' => null,
            'files/spreadsheet/empty.xls' => '6ed659132105ff18df7946a66ac1853f693ac93504d1f21b82d0b0514d1f7ed0',
            'files/spreadsheet/empty.xlsx' => '4eb8bea601f7673e25c11ecf8cd18e2535a194f06f3df09ea238e89bf16cd7d7',
            'files/presentation/empty.odp' => 'c663570ba816d2ec8813ae02e5a78257a3a56a8f60e7ad657119e6f0052a26fe',
            'files/presentation/empty.pptx' => '778847dd7f5802f602032a00c8da248062659a3344b93816489fc003f57f21dd',
            'files/text/empty.csv' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            'files/text/empty.json' => 'ca3d163bab055381827226140568f3bef7eaac187cebd76878e0b63e9e442356',
            'files/text/empty.txt' => 'b42f2099187886def637d6aa840022266e05cb6c987a9394e708e23cd505eb46',
            'files/image/empty.bmp' => '1a011d90fff8ac4b581f95472633c311a50143ba5e1020fcd9d578fe921d7c99',
            'files/image/empty.gif' => '84f7a1205ca382c044859ada51473fda5d972083f0ab5caf0e61309e2fbbc5d1',
            'files/image/empty.jpg' => 'dc0918fcf7dc57eaef1b0bb69bc1b88e1d10422a38e367565e001306407e41ac',
            'files/image/empty.png' => '2024896e28f508d6b695fffad2531a2718c1e46b6c2c924d9b77f10ac2688793',
            'files/markup/empty.html' => '5e2ab2f655e9378fd1e54a4bfd81cece72a3bdeb04c87be86041962fe5c3bd3c',
            'files/markup/empty.xml' => '4be690ad5983b2a40f640481fdb27dcc43ac162e14fa9aab2ff45775521d9213',
        ];
    }

    /**
     * @testWith ["install"]
     *           ["update"]
     */
    public function testDownload(string $command): void
    {
        $this->assertFiles(false);
        self::runComposer($command);
        $this->assertFiles(true);
        $this->assertPharExecutable();
    }

    private function assertFiles(bool $exist = true): void
    {
        foreach ($this->getFileChecksums() as $file => $sha256) {
            if ($exist && $sha256) {
                $this->assertFileExists($file);
                $this->assertEquals($sha256, hash('sha256', file_get_contents($file)));
            } else {
                $this->assertFileDoesNotExist($file);
            }
        }
    }

    private function assertPharExecutable(): void
    {
        $process = new Process([\PHP_OS_FAMILY === 'Windows' ? 'files/phar/hello.bat' : 'files/phar/hello']);
        $process->run();
        $this->assertSame('Hello', $process->getOutput());
        $this->assertSame(0, $process->getExitCode());
    }

    /**
     * Create a temp folder with a "composer.json" file and chdir() into it.
     */
    private static function initTestProject(): string
    {
        self::$origDir = getcwd();
        $testDir = getenv('USE_TEST_PROJECT');
        if (\is_string($testDir)) {
            self::$testDir = $testDir;
            @unlink(self::$testDir.\DIRECTORY_SEPARATOR.'composer.lock');
        } else {
            self::$testDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'assetplg-'.md5(__DIR__.time().random_int(0, 10000));
            self::cleanDir(self::$testDir);
        }

        if (!is_dir(self::$testDir)) {
            mkdir(self::$testDir);
        }
        file_put_contents(self::$testDir.\DIRECTORY_SEPARATOR.'composer.json', json_encode(static::getComposerJson(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        chdir(self::$testDir);

        return self::$testDir;
    }

    private static function cleanTestProjectDir(): void
    {
        if (self::$testDir) {
            chdir(self::$origDir);
            self::$origDir = null;

            if (getenv('USE_TEST_PROJECT')) {
                fwrite(\STDERR, sprintf("\n\nTest project location (%s): %s\n", self::class, self::$testDir));
            } else {
                self::cleanDir(self::$testDir);
            }
            self::$testDir = null;
        }
    }

    private static function startLocalServer(): void
    {
        self::$server = new Process(['php', '-S', 'localhost:8000', '-t', static::getFixturesPath()]);
        self::$server->start();
        self::$server->waitUntil(function ($type, $output) {
            return false !== strpos($output, 'Development Server (http://localhost:8000) started');
        });
    }

    private static function stopLocalServer(): void
    {
        if (self::$server) {
            self::$server->stop();
            self::$server = null;
        }
    }

    private static function runComposer(string $command = 'install'): void
    {
        $process = new Process([self::getComposerPath(), $command, '-v']);
        $process->run(getenv('DEBUG_COMPOSER') ? function ($type, $buffer) {
            echo $buffer;
        } : null);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private static function cleanDir(string $dir): void
    {
        $process = Process::fromShellCommandline(
            \PHP_OS_FAMILY === 'Windows'
            ? 'if exist "${:DIR}" ( rm -rf "${:DIR}" )'
            : 'if [ -d "${:DIR}" ]; then rm -rf "${:DIR}" ; fi'
        );
        $process->run(null, ['DIR' => $dir]);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private static function getComposerPath(): string
    {
        return realpath(__DIR__.'/../../vendor/bin/composer');
    }

    private static function getPluginSourceDir(): string
    {
        return realpath(__DIR__.'/../..');
    }

    private static function getFixturesPath(): string
    {
        return realpath(__DIR__.'/../Fixtures');
    }
}
