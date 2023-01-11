<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\VariablesFilter;

class VariablesFilterTest extends BaseFilterTestCase
{
    public function getEmptyVariablesTests(): array
    {
        return [
            [[]],
            [['variables' => []]],
        ];
    }

    /**
     * @dataProvider getEmptyVariablesTests
     */
    public function testEmptyVariables(array $extraFile): void
    {
        $this->assertSame([
            '{$id}' => $this->name,
            '{$version}' => '',
        ], $this->filter->filter($extraFile));
    }

    public function getInvalidVariablesTests(): array
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
     * @dataProvider getInvalidVariablesTests
     */
    public function testInvalidVariables(mixed $invalidVariables, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('must be array, "%s" given', $type));
        $this->filter->filter([
            'variables' => $invalidVariables,
        ]);
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
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid: Variable key "%s" should be this format "{$variable-name}"', $invalidVariableKey));
        $this->filter->filter([
            'variables' => [
                $invalidVariableKey => '"baz"',
            ],
        ]);
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
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid: Expression "%s" should be evaluated to string, "%s" given', $invalidVariableValue, $type));
        $this->filter->filter([
            'variables' => [
                '{$baz}' => $invalidVariableValue,
            ],
        ]);
    }

    public function getInvalidVariableExpressionSyntaxTests(): array
    {
        return [
            ['in_array(1, range(1, 10)', 'expected closing `)`'],
            ["'foo' in ['foo', 'baz']", 'unexpected end of string'],
            ["invalid('test')", 'var `invalid` not defined'],
            ["PHP_OS('test')", '`PHP_OS` is not callable'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableExpressionSyntaxTests
     */
    public function testInvalidVariableExpressionSyntax(string $expression, string $reason): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid. There is an error while evaluating expression "%s": %s', $expression, $reason));
        $this->filter->filter([
            'variables' => [
                '{$baz}' => $expression,
            ],
        ]);
    }

    public function testFilterVariables(): void
    {
        $expectedVariables = [
            '{$id}' => $this->name,
            '{$version}' => '1.2.3',
            '{$foo}' => 'foo',
            '{$baz}' => 'baz3',
        ];
        $this->assertEquals($expectedVariables, $this->filter->filter([
            'variables' => [
                '{$foo}' => '"foo"',
                '{$baz}' => '"baz"~1+2',
            ],
            'version' => '1.2.3',
        ]));
        $this->assertSame($expectedVariables, $this->filter->filter([]));
    }

    protected function createFilter(): FilterInterface
    {
        return new VariablesFilter($this->name, $this->parent);
    }
}
