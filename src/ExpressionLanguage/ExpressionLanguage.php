<?php

namespace LastCall\DownloadsPlugin\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{
    protected function registerFunctions()
    {
        // Don't register constant function
    }
}
