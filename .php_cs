<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->notPath('resources')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    'header_comment' => [
        'comment_type' => 'PHPDoc',
        'header' => file_get_contents(__DIR__.'/.header_stamp.txt'),
        'location' => 'after_open',
        'separate' => 'bottom'
    ]
])
    ->setFinder($finder)
    ;
