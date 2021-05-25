<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->notPath('resources');

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true);
return $config->setRules([
    '@Symfony' => true,
    'header_comment' => [
        'comment_type' => 'PHPDoc',
        'header' => file_get_contents(__DIR__ . '/.header_stamp.txt'),
        'location' => 'after_open',
        'separate' => 'bottom'
    ],
    'concat_space' => [
        'spacing' => 'one'
    ],
    'final_class' => true,
    'static_lambda' => true,
    'return_assignment' => true,
    'phpdoc_var_annotation_correct_order' => true,
    'array_syntax' => true,
    'clean_namespace' => true,
    'list_syntax' => true,
    'ternary_to_null_coalescing' => true,
])
    ->setFinder($finder);
