<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class CommandTestCase extends TestCase
{
    private static ?string $origDir;
    protected static ?string $testDir;
    private static ?Process $server;

    protected static function getComposerJson(): array
    {
        return [
            'name' => 'test/project',
            'repositories' => [
                'composer-downloads-plugin' => [
                    'type' => 'path',
                    'url' => self::getPluginSourceDir(),
                ],
                'library' => [
                    'type' => 'path',
                    'url' => self::getLibraryPath(),
                    'options' => [
                        'symlink' => false,
                    ],
                ],
            ],
            'require' => [
                'tienvx/composer-downloads-plugin' => '@dev',
                'test/library' => '@dev',
            ],
            'minimum-stability' => 'dev',
            'extra' => [
                'downloads' => [
                    '*' => [
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
                        'url' => 'http://localhost:8000/archive/empty.xml.gz',
                        'path' => 'files/markup/empty.xml',
                    ],
                    'html' => [
                        'url' => 'http://localhost:8000/archive/empty.html.gz',
                        'path' => 'files/markup/empty.html',
                    ],
                ],
            ],
            // Bin defined in root package does not have any affects.
            'bin' => [
                'files/phar/hello',
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
        self::cleanDir(self::getPathToTestDir('files'));
        self::cleanDir(self::getPathToTestDir('vendor/test/library/files'));
        self::cleanDir(self::getPathToTestDir('vendor/bin'));
    }

    protected static function getPathToTestDir(string $path = ''): string
    {
        return self::$testDir.\DIRECTORY_SEPARATOR.$path;
    }

    protected function getFilesFromProject(): array
    {
        return [
            // From project
            'files/phar/hello' => '66ef5d9bd7854d96e0c3b05e8c169a5fbd398ece5299032c132387edb87cf491',
            'files/phar/hello.bat' => \PHP_OS_FAMILY === 'Windows',
            'files/file/ipsum' => \PHP_OS_FAMILY === 'Windows'
                ? '77559b8e3cf8082554f5cb314729363017de998b63f0ab9cb751246c167d7bdd'
                : '77bdfb1d37ee5a5e6d08d0bd8f2d4abfde6b673422364ba9ad432deb2d9c6e4d', // New line chars are replaced in Windows
            'files/doc/v1.2.3/empty.doc' => '60b5e45db3b51c38a5b762e771ee2f19692f52186c42c3930d56bbdf04d21f4e',
            'files/doc/v1.2.3/empty.docx' => '61cdb4b8b9067ab1f4eaa5ba782007c81bdd04283a228b5076aeeb4c9362020b',
            'files/doc/v1.3.0/empty.doc' => '60b5e45db3b51c38a5b762e771ee2f19692f52186c42c3930d56bbdf04d21f4e',
            'files/doc/v1.3.0/empty.docx' => '61cdb4b8b9067ab1f4eaa5ba782007c81bdd04283a228b5076aeeb4c9362020b',
            'files/doc/v1.3.0/empty.pdf' => 'c9a7a7f01b8909dbc5405a6c37372610b853bc76464f167e964c217d8ffdcc3c',
            'files/doc/v1.3.0/empty.txt' => false,
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
            'vendor/bin/hello' => false,
            'vendor/bin/hello.bat' => false,
        ];
    }

    protected function getFilesFromLibrary(): array
    {
        return [
            // From library
            'vendor/test/library/files/php/hello-php' => \PHP_OS_FAMILY === 'Windows'
                ? '6094c815897bac5498a356d6c93272b16cc2745ac643129aba55fe429cb0622f'
                : '27e82fb9cc729a9f535e7ad26364f108aabaafa783617d5bce51ba986ad85adb', // New line chars are replaced in Windows
            'vendor/test/library/files/php/hello-php.bat' => \PHP_OS_FAMILY === 'Windows',
            'vendor/test/library/files/ruby/hello-ruby' => \PHP_OS_FAMILY === 'Windows'
                ? '5f53359b554adb060f9592541494f46f0947f27d0c07962b4b559f1d548a32f2'
                : 'df6261c52e25ad8bc5db62bbfb335631b554a99e8842535bf69b96d08ad37939', // New line chars are replaced in Windows
            'vendor/test/library/files/ruby/hello-ruby.bat' => \PHP_OS_FAMILY === 'Windows',
            'vendor/test/library/files/mix/bin/hello-python' => '5e2820a0a75ec820e57de0ac2fc56a5ed409153f68915eef02f4373decb5df73',
            'vendor/test/library/files/mix/bin/hello-python.bat' => \PHP_OS_FAMILY === 'Windows',
            'vendor/test/library/files/mix/doc/empty.epub' => 'cae703a1c8173e65efae5accada6ce92a40dddf5fd3761b6ca7bd51c77eea29a',
            'vendor/test/library/files/mix/img/empty.svg' => 'c276389006b7ab53a33cacc4a04a62bcfa050d9cc34fd90f1aefc119fa1803fe',
            'vendor/bin/hello-php' => true,
            'vendor/bin/hello-php.bat' => \PHP_OS_FAMILY === 'Windows',
            'vendor/bin/hello-ruby' => true,
            'vendor/bin/hello-ruby.bat' => \PHP_OS_FAMILY === 'Windows',
            'vendor/bin/hello-python' => true,
            'vendor/bin/hello-python.bat' => \PHP_OS_FAMILY === 'Windows',
        ];
    }

    protected function getFiles(): array
    {
        return array_merge($this->getFilesFromProject(), $this->getFilesFromLibrary());
    }

    protected function runComposerCommandAndAssert(array $command): void
    {
        $this->assertFiles($this->shouldExistBeforeCommand());
        $this->runComposer($command);
        $this->assertFiles($this->shouldExistAfterCommand());
        $this->assertExecutable();
    }

    protected function shouldExistBeforeCommand(): bool
    {
        return false;
    }

    protected function shouldExistAfterCommand(): bool
    {
        return true;
    }

    protected function assertFiles(bool $exist = true): void
    {
        foreach ($this->getFiles() as $file => $sha256) {
            if ($exist && $sha256) {
                $this->assertFileExists(self::getPathToTestDir($file));
                if (\is_string($sha256)) {
                    $this->assertEquals($sha256, hash('sha256', file_get_contents(self::getPathToTestDir($file))));
                }
            } else {
                $this->assertFileDoesNotExist(self::getPathToTestDir($file));
            }
        }
    }

    protected function getExecutableFilesFromProject(): array
    {
        return [
            'files/phar/hello' => 'Hello from phar file!',
        ];
    }

    protected function getExecutableFilesFromLibrary(): array
    {
        return [
            'vendor/test/library/files/php/hello-php' => 'Hello from php file!',
            'vendor/test/library/files/ruby/hello-ruby' => 'Hello from ruby file!'.$this->eol(),
            'vendor/test/library/files/mix/bin/hello-python' => 'Hello from python file!'.$this->eol(),
            'vendor/bin/hello-php' => 'Hello from php file!',
            'vendor/bin/hello-ruby' => 'Hello from ruby file!'.$this->eol(),
            'vendor/bin/hello-python' => 'Hello from python file!'.$this->eol(),
        ];
    }

    protected function getExecutableFiles(): array
    {
        return array_merge($this->getExecutableFilesFromProject(), $this->getExecutableFilesFromLibrary());
    }

    protected function getMissingExecutableFiles(): array
    {
        return [];
    }

    protected function assertExecutable(): void
    {
        foreach ($this->getExecutableFiles() as $file => $output) {
            $process = new Process([self::getPathToTestDir($file)]);
            $process->run();
            $this->assertSame($output, $process->getOutput());
            $this->assertSame(0, $process->getExitCode());
        }
        foreach ($this->getMissingExecutableFiles() as $file) {
            $process = new Process([self::getPathToTestDir($file)]);
            $process->run();
            $this->assertSame('', $process->getOutput());
            $exitCodeMap = [
                'Windows' => 1,
                'Darwin' => 126,
                'Linux' => 127,
            ];
            $this->assertSame($exitCodeMap[\PHP_OS_FAMILY], $process->getExitCode());
        }
    }

    /**
     * Create a temp folder with a "composer.json" file and chdir() into it if needed.
     */
    protected static function initTestProject(): void
    {
        self::$origDir = getcwd();
        $testDir = getenv('USE_TEST_PROJECT');
        if (\is_string($testDir)) {
            self::$testDir = $testDir;
            @unlink(self::getPathToTestDir('composer.lock'));
        } else {
            self::$testDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'assetplg-'.md5(__DIR__.time().random_int(0, 10000));
            self::cleanDir(self::$testDir);
        }

        if (!is_dir(self::$testDir)) {
            mkdir(self::$testDir);
        }
        file_put_contents(self::getPathToTestDir('composer.json'), json_encode(static::getComposerJson(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        static::shouldChangeDir() && chdir(self::$testDir);
    }

    protected static function cleanTestProjectDir(): void
    {
        if (self::$testDir) {
            static::shouldChangeDir() && chdir(self::$origDir);
            self::$origDir = null;

            if (getenv('USE_TEST_PROJECT')) {
                fwrite(\STDERR, sprintf("\n\nTest project location (%s): %s\n", self::class, self::$testDir));
            } else {
                self::cleanDir(self::$testDir);
            }
            self::$testDir = null;
        }
    }

    protected static function shouldChangeDir(): bool
    {
        return true;
    }

    protected static function startLocalServer(): void
    {
        self::$server = new Process(['php', '-S', 'localhost:8000', '-t', static::getFilesPath()]);
        self::$server->start();
        self::$server->waitUntil(function ($type, $output) {
            return false !== strpos($output, 'Development Server (http://localhost:8000) started');
        });
    }

    protected static function stopLocalServer(): void
    {
        if (self::$server) {
            self::$server->stop();
            self::$server = null;
        }
    }

    private function runComposer(array $command): void
    {
        $process = new Process([self::getComposerPath(), ...$command, '-v']);
        $process->run(getenv('DEBUG_COMPOSER') ? function ($type, $buffer) {
            echo $buffer;
        } : null);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->assertComposerErrorOutput($process->getErrorOutput());
    }

    protected function assertComposerErrorOutput(string $output): void
    {
    }

    protected static function cleanDir(string $dir): void
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

    protected static function getComposerPath(): string
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

    private static function getFilesPath(): string
    {
        return realpath(self::getFixturesPath().'/files');
    }

    private static function getLibraryPath(): string
    {
        return realpath(self::getFixturesPath().'/library');
    }

    protected function eol(): string
    {
        return \PHP_OS_FAMILY === 'Windows' ? "\r\n" : "\n";
    }
}
