<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class SniffTest.
 */
class SniffTest extends IntegrationTestCase
{
    public static function getComposerJson(): array
    {
        return parent::getComposerJson() + [
            'name' => 'test/sniff-test',
            'require' => [
                'civicrm/composer-downloads-plugin' => '@dev',
            ],
            'minimum-stability' => 'dev',
            'extra' => [
                'downloads' => [
                    '*' => [
                        'path' => 'extern/{$id}',
                    ],
                    'README' => [
                        'url' => 'https://github.com/composer/composer/raw/1.9.0/README.md',
                        'path' => 'docs/README.md',
                    ],
                    'jquery-full' => [
                        'url' => 'https://github.com/civicrm/jquery/archive/1.12.4-civicrm-1.2.zip',
                    ],
                    'jquery-lesser' => [
                        'version' => '1.12.4-civicrm-1.2',
                        'url' => 'https://github.com/civicrm/jquery/archive/{$version}.zip',
                        'path' => 'extern/jquery-lesser',
                        'ignore' => ['Gruntfile.js'],
                    ],
                    'cv' => [
                        'type' => 'phar',
                        'url' => 'https://download.civicrm.org/cv/cv.phar-2019-08-20-14fe9da8',
                        'path' => 'bin/cv',
                    ],
                    'symfony-cli' => [
                        'type' => 'archive',
                        'version' => 'v5.4.13',
                        'variables' => [
                            '{$os}' => 'strtolower(PHP_OS_FAMILY)',
                            '{$architecture}' => 'PHP_OS_FAMILY === "Darwin" ? "all" : (in_array(php_uname("m"), ["AMD64", "x86_64"]) ? "amd64" : php_uname("m"))',
                            '{$extension}' => 'PHP_OS_FAMILY === "Windows" ? "zip" : "tar.gz"',
                        ],
                        'url' => 'https://github.com/symfony-cli/symfony-cli/releases/download/{$version}/symfony-cli_{$os}_{$architecture}.{$extension}',
                        'path' => 'extern/{$id}',
                        'ignore' => ['LICENSE', 'README.md'],
                    ],
                    'jpeg.documents' => [
                        'url' => 'https://www.ijg.org/files/jpeg.documents.gz',
                        'path' => 'docs/jpeg',
                    ],
                ],
            ],
            'config' => [
                'allow-plugins' => [
                    'civicrm/composer-downloads-plugin' => true,
                ],
            ],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::initTestProject(static::getComposerJson());
        $composerPath = self::getComposerPath();
        self::runOk([$composerPath, 'install', '-v']);
    }

    public function getExampleChecksums(): array
    {
        $fileMap = [
            'Windows' => [
                'file' => 'extern/symfony-cli/symfony.exe',
                'sha256' => '6f501d969cce70d29a46f56d3e5de855303700bf0480260217cf9a1954fbbe6c',
            ],
            'Darwin' => [
                'file' => 'extern/symfony-cli/symfony',
                'sha256' => 'db09d77a341bc77f76ec1162d315c117aca846e5e2df5e10e8c2b71d3cf26953',
            ],
            'Linux' => [
                'file' => 'extern/symfony-cli/symfony',
                'sha256' => '43e24206a9c5d89e515206041fc28bddf665c4b262c0ec81d5df51dc1261829a',
            ],
        ];

        return [
            ['docs/README.md', 'docs/README.md', '1d0577cc52d55f0680b431184e898f0cbcb927e52e843a319d7122db9be72813'],
            ['extern/jquery-full', 'extern/jquery-full/dist/jquery.js', '5f2caf09052782caf67e1772c0abce31747ffbc7a1c50690e331b99c7d9ea8dc'],
            ['extern/jquery-full', 'extern/jquery-full/Gruntfile.js', '3508ff74f8ef106a80f25f28f44a20c47a2b67d84396bb141928ff978ba4012e'],
            ['extern/jquery-lesser', 'extern/jquery-lesser/dist/jquery.js', '5f2caf09052782caf67e1772c0abce31747ffbc7a1c50690e331b99c7d9ea8dc'],
            ['extern/jquery-lesser', 'extern/jquery-lesser/Gruntfile.js', null],
            ['bin/cv', 'bin/cv', 'bf162d5d7dd0bef087d7dd07f474039b2e25c4bcca328a2b2097958ac6294476'],
            ['extern/symfony-cli', $fileMap[\PHP_OS_FAMILY]['file'], $fileMap[\PHP_OS_FAMILY]['sha256']],
            ['docs/jpeg', 'docs/jpeg/jpeg.documents', '551ffc93efc23d3eab35a8b4347273dd44664ee4e1419d7cb732fa9756c9bf87'],
        ];
    }

    /**
     * Ensure that the file checksums match expectations with both (a) original download and (b) re-download.
     *
     * @param string|null $sha256 The expected content of the file, or NULL if the file should not exist
     *
     * @dataProvider getExampleChecksums
     */
    public function testDownloadAndRedownload(string $path, string $file, ?string $sha256): void
    {
        // Initial download
        $this->assertFileChecksum($file, $sha256, 'Initial');

        // Force re-download
        if (is_dir($path)) {
            self::cleanDir($path);
        } else {
            unlink($path);
        }
        $this->assertFileDoesNotExist($file);
        $composerPath = self::getComposerPath();
        self::runOk([$composerPath, 'install', '-v']);

        // And make sure it all worked out...
        $this->assertFileChecksum($file, $sha256, 'Redownload');
    }

    public function assertFileChecksum($file, $sha256, $message = null): void
    {
        if (null === $sha256) {
            $this->assertFileDoesNotExist($file, "($message) File should not exist");
        } else {
            $this->assertFileExists($file, "($message) File should exist");
            $this->assertEquals($sha256, hash('sha256', file_get_contents($file)), "($message) File should given checksum");
        }
    }

    private static function getComposerPath(): string
    {
        return realpath(__DIR__.'/../../vendor/bin/composer');
    }

    private static function runOk(array $command): void
    {
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
