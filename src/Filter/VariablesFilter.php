<?php

namespace LastCall\DownloadsPlugin\Filter;

use Le\SMPLang\SMPLang;

class VariablesFilter extends BaseFilter
{
    protected function get(array $extraFile): array
    {
        $variables = [
            '{$id}' => $this->subpackageName,
            '{$version}' => $extraFile['version'] ?? '',
        ];

        if (!isset($extraFile['variables'])) {
            return $variables;
        }

        $values = $extraFile['variables'];
        if (!\is_array($values)) {
            $this->throwException('variables', sprintf('must be array, "%s" given', get_debug_type($values)));
        }

        if (empty($values)) {
            return $variables;
        }

        $smpl = new SMPLang([
            'range' => \Closure::fromCallable('range'),
            'strtolower' => \Closure::fromCallable('strtolower'),
            'php_uname' => \Closure::fromCallable('php_uname'),
            'in_array' => \Closure::fromCallable('in_array'),
            'str_contains' => \Closure::fromCallable('str_contains'),
            'str_starts_with' => \Closure::fromCallable('str_starts_with'),
            'str_ends_with' => \Closure::fromCallable('str_ends_with'),
            'matches' => fn (string $pattern, string $subject) => 1 === preg_match($pattern, $subject),
            'PHP_OS' => \PHP_OS,
            'PHP_OS_FAMILY' => \PHP_OS_FAMILY,
            'PHP_SHLIB_SUFFIX' => \PHP_SHLIB_SUFFIX,
            'DIRECTORY_SEPARATOR' => \DIRECTORY_SEPARATOR,
        ]);
        foreach ($values as $key => $value) {
            if (!preg_match('/^{\$[^}]+}$/', $key)) {
                throw new \UnexpectedValueException(sprintf('Expected variable key in this format "{$variable-name}", "%s" given.', $key));
            }
            $result = $smpl->evaluate($value);
            if (!\is_string($result)) {
                throw new \UnexpectedValueException(sprintf('Expected the the result of expression "%s" to be a string, "%s" given.', $value, get_debug_type($result)));
            }
            $variables[$key] = $result;
        }

        return $variables;
    }
}
