<?php

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODE_QUALITY_STRICT);
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::EARLY_RETURN);
    $containerConfigurator->import(SetList::PRIVATIZATION);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);
    $containerConfigurator->import(SetList::PSR_4);
    $containerConfigurator->import(SetList::MYSQL_TO_MYSQLI);
    $containerConfigurator->import(SetList::TYPE_DECLARATION_STRICT);
    $containerConfigurator->import(SetList::UNWRAP_COMPAT);

    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::PHP_74);
    $containerConfigurator->import(SetList::PHP_80);

    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::PATHS,
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/rector.php',
        ]
    );

    $parameters->set(Option::AUTO_IMPORT_NAMES, false);
    $parameters->set(Option::AUTOLOAD_PATHS, [__DIR__ . '/../Classes']);
    $parameters->set(
        Option::SKIP,
        [
            RecastingRemovalRector::class,
            ConsistentPregDelimiterRector::class,
            PostIncDecToPreIncDecRector::class,
            FinalizeClassesWithoutChildrenRector::class,
            ChangeOrIfReturnToEarlyReturnRector::class,
            ChangeAndIfToEarlyReturnRector::class,
            ReturnBinaryAndToEarlyReturnRector::class,
            MakeBoolPropertyRespectIsHasWasMethodNamingRector::class,
            MoveOutMethodCallInsideIfConditionRector::class,
            ReturnArrayClassMethodToYieldRector::class,
            AddArrayParamDocTypeRector::class,
            AddArrayReturnDocTypeRector::class,
            ReturnFluentChainMethodCallToNormalMethodCallRector::class,
            IssetOnPropertyObjectToPropertyExistsRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            RepeatedLiteralToClassConstantRector::class,
            RenameVariableToMatchNewTypeRector::class,
            AddLiteralSeparatorToNumberRector::class,
            RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,
            ChangeReadOnlyVariableWithDefaultValueToConstantRector::class,
            PrivatizeLocalPropertyToPrivatePropertyRector::class,
            RemoveDelegatingParentCallRector::class,

            // @todo strict php
            ArgumentAdderRector::class,
            ParamTypeDeclarationRector::class,
            ReturnTypeDeclarationRector::class,
            RemoveExtraParametersRector::class,
            EncapsedStringsToSprintfRector::class,
            AddFalseDefaultToBoolPropertyRector::class,
            WrapEncapsedVariableInCurlyBracesRector::class,
            UseIdenticalOverEqualWithSameTypeRector::class,
        ]
    );

    $services = $containerConfigurator->services();
    $services->set(RemoveUnusedPrivatePropertyRector::class);
};
