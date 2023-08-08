<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/ecs.php',
        ]
    );

    $ecsConfig->import(SetList::COMMON);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::SYMPLIFY);

    $ecsConfig->services()
        ->set(LineLengthFixer::class)
        ->call('configure', [[
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]]);

    $ecsConfig->indentation('spaces');
    $ecsConfig->lineEnding(PHP_EOL);
    $ecsConfig->cacheDirectory('.cache/ecs/default/');

    // Skip Rules and Sniffer
    $ecsConfig->skip(
        [
            // Default Skips
            Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer::class => [
                __DIR__ . '/ecs.php',
            ],
            ArrayListItemNewlineFixer::class => null,
            ArrayOpenerAndCloserNewlineFixer::class => null,
            ClassAttributesSeparationFixer::class => null,
            OrderedImportsFixer::class => null,
            NotOperatorWithSuccessorSpaceFixer::class => null,
            ExplicitStringVariableFixer::class => null,
            ArrayIndentationFixer::class => null,
            DocBlockLineLengthFixer::class => null,
            '\SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff.DuplicateSpaces' => null,
            '\SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff.PartialUse' => null,
        ]
    );
};
