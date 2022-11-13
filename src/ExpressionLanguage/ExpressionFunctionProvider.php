<?php

namespace LastCall\DownloadsPlugin\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            ExpressionFunction::fromPhp('strtolower'),
            ExpressionFunction::fromPhp('php_uname'),
        ];
    }
}
