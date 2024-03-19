<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
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
        DocBlockLineLengthFixer::class => null,
        ArrayListItemNewlineFixer::class => null,
        ArrayOpenerAndCloserNewlineFixer::class => null,
        FunctionTypehintSpaceFixer::class => [
            __DIR__ . '/../Tests/Unit/TYPO3/AdditionalResponseHeadersTest.php',
            __DIR__ . '/../Classes/TYPO3/Hooks/ClearCacheMenuHook.php',
            __DIR__ . '/../Classes/TYPO3/Configuration/ExtensionConfiguration.php',
        ],
        DeclareStrictTypesFixer::class => null,
        GeneralPhpdocAnnotationRemoveFixer::class => null,
        RenameParamToMatchTypeRector::class => null,

    ])
    ->withSpacing(OPTION::INDENTATION_SPACES, "\n");
