<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()

    ->withRules([
        NoUnusedImportsFixer::class,
        TrailingCommaInMultilineFixer::class,
    ])

    ->withConfiguredRule(
        MethodArgumentSpaceFixer::class,
        [
            'on_multiline' => 'ensure_fully_multiline',   // klíčové nastavení
            'keep_multiple_spaces_after_comma' => false,
        ],
    )

    ->withPhpCsFixerSets(
        doctrineAnnotation: true,
        perCS20: true,
    )

    ->withPreparedSets(
        arrays: true,
        comments: true,
        docblocks: true,
        namespaces: true,
    );
