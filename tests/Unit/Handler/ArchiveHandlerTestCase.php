<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

abstract class ArchiveHandlerTestCase extends BaseHandlerTestCase
{
    public function getBinariesTests(): array
    {
        return [
            [null, []],
            [[], []],
            [['bin/file1'], ['bin/file1']],
            [['bin/file1', 'bin/file2'], ['bin/file1', 'bin/file2']],
        ];
    }

    public function getInvalidBinariesTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    protected function getTrackingFile(): string
    {
        return $this->targetPath.\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }

    public function getInvalidIgnoreTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidIgnoreTests
     */
    public function testInvalidIgnore(mixed $ignore, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $handler = $this->createHandler($this->parent, $this->parentPath, $this->extraFile + ['ignore' => $ignore]);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Attribute "ignore" of extra file "%s" defined in package "%s" must be array, "%s" given.', $this->id, $this->parentName, $type));
        $handler->getTrackingData();
    }
}
