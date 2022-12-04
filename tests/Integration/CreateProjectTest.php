<?php

namespace LastCall\DownloadsPlugin\Tests\Integration;

class CreateProjectTest extends CommandTestCase
{
    private static ?string $tmpDir;
    protected static bool $needChangeDir = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert([
            'create-project',
            'test/project',
            self::getPathToTestDir(),
            '--repository',
            json_encode([
                'type' => 'path',
                'url' => self::$tmpDir,
                'options' => [
                    'symlink' => false,
                ],
            ]),
            '--stability=dev',
        ]);
    }

    public static function setUpBeforeClass(): void
    {
        self::startLocalServer();
        self::initTestProject();
        self::initTempProject();
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanTestProjectDir();
        self::cleanTempProjectDir();
        self::stopLocalServer();
    }

    protected function setUp(): void
    {
    }

    protected static function initTestProject(): void
    {
        $testDir = getenv('USE_TEST_PROJECT');
        if (\is_string($testDir)) {
            self::$testDir = $testDir;
            if (is_dir($testDir)) {
                throw new \UnexpectedValueException(sprintf('Test project directory "%s" must not exist.', $testDir));
            }
        } else {
            self::$testDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('test-project-', true);
            self::cleanDir(self::$testDir);
        }
    }

    private static function initTempProject(): void
    {
        self::$tmpDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('temp-dir-', true);
        if (!is_dir(self::$tmpDir)) {
            mkdir(self::$tmpDir);
        }
        file_put_contents(self::getPathToTempDir('composer.json'), json_encode(static::getComposerJson(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
    }

    private static function cleanTempProjectDir(): void
    {
        if (self::$tmpDir) {
            self::cleanDir(self::$tmpDir);
            self::$tmpDir = null;
        }
    }

    private static function getPathToTempDir(string $path = ''): string
    {
        return self::$tmpDir.\DIRECTORY_SEPARATOR.$path;
    }
}
