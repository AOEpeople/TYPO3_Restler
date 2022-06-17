<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::PATHS,
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/ecs.php',
        ]
    );

    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMPLIFY);

    $containerConfigurator->services()
        ->set(LineLengthFixer::class)
        ->call('configure', [[
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]]);

    // Skip Rules and Sniffer
    $parameters->set(
        Option::SKIP,
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

            // @todo for next upgrade
            NoSuperfluousPhpdocTagsFixer::class => null,
            // @todo strict php
            DeclareStrictTypesFixer::class => null,
            StrictComparisonFixer::class => null,
            StrictParamFixer::class => null,
        ]
    );
};
