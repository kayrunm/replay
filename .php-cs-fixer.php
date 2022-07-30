<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'braces' => ['allow_single_line_closure' => true],
        'concat_space' => ['spacing' => 'one'],
        'native_function_invocation' => false,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_order' => true,
        'yoda_style' => false,
    ])
    ->setFinder(
        Finder::create()->in(__DIR__)
    )
;
