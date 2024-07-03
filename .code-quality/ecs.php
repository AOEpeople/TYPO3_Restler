<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Tests',
        __DIR__ . '/ecs.php',
    ])
    ->withSets([
        SetList::PSR_12,
        SetList::COMMON,
        SetList::SYMPLIFY,
        SetList::CLEAN_CODE,
    ])
    ->withConfiguredRule(
        LineLengthFixer::class,
        [
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]
    )
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class => null,
        ArrayListItemNewlineFixer::class => null,
        ArrayOpenerAndCloserNewlineFixer::class => null,
        GeneralPhpdocAnnotationRemoveFixer::class => null,
        RenameParamToMatchTypeRector::class => null,
    ])
    ->withSpacing(OPTION::INDENTATION_SPACES, "\n");
