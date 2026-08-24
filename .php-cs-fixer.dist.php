<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true),
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        'declare_strict_types' => false,
        'final_class' => false,
        'global_namespace_import' => true,
        'list_syntax' => ['syntax' => 'short'],
        'array_syntax' => ['syntax' => 'short'],
        'constant_case' => ['case' => 'lower'],
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'ordered_interfaces' => true,
        'phpdoc_order' => true,
        'single_import_per_statement' => true,
        'ordered_class_elements' => ['sort_algorithm' => 'alpha',],
        'yoda_style' => false,
        'is_null' => false,
        'native_function_invocation' => false,
        'phpdoc_to_comment' => false,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'return_assignment' => true,
        'ordered_types' => ['null_adjustment' => 'always_last',],
        'phpdoc_array_type' => true,
    ]);