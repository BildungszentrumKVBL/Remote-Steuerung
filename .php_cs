<?php

$config = PhpCsFixer\Config::create()
    ->setRules(
        [
            '@Symfony'                => true,
            'align_multiline_comment' => true,
            'array_syntax'            => ['syntax'  => 'short'],
            'binary_operator_spaces'  => ['default' => 'align'],
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['vendor', 'var', 'app', 'assets', 'config', 'public', 'node_modules', 'translations'])
            ->in(__DIR__)
    )
    ->setCacheFile(__DIR__.'/var/.php_cs.cache');

return $config;
