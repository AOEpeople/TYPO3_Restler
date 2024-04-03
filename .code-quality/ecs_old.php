<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/../Classes',
        __DIR__ . '/ecs.php',
    ])
    ->withSets([
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::PSR_12,
        SetList::SYMPLIFY,
    ])
    ->withConfiguredRule(
        LineLengthFixer::class,
        [
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]
    )
    ->withSkip([
        // Default Skips
        Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer::class => [
            __DIR__ . '/ecs.php',
        ],
        ArrayListItemNewlineFixer::class => null,
        ArrayOpenerAndCloserNewlineFixer::class => null,
        ClassAttributesSeparationFixer::class => null,
        GeneralPhpdocAnnotationRemoveFixer::class => null,
        OrderedImportsFixer::class => null,
        NotOperatorWithSuccessorSpaceFixer::class => null,
        ExplicitStringVariableFixer::class => null,
        ArrayIndentationFixer::class => null,

        // @todo for next upgrade
        NoSuperfluousPhpdocTagsFixer::class => null,

        // @todo strict php
        DeclareStrictTypesFixer::class => null,
        StrictComparisonFixer::class => null,
        StrictParamFixer::class => null,
    ]);
