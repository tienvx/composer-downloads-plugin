<?php

$config = new PhpCsFixer\Config();

return $config
  ->setRiskyAllowed(true)
  ->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
  ])
  ->setLineEnding(PHP_EOL)
  ->setFinder(
    PhpCsFixer\Finder::create()
      ->in([__DIR__.'/src', __DIR__.'/tests'])
  );
