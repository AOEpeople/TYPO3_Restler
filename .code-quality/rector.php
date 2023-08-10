<?php

declare(strict_types=1);

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector;
use Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Tests',
            __DIR__ . '/../code-quality',
        ]
    );

    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);

    $rectorConfig->importNames(false);
    $rectorConfig->autoloadPaths([__DIR__ . '/../Classes']);
    $rectorConfig->cacheDirectory('.cache/rector/default/');
    $rectorConfig->skip(
        [
            RecastingRemovalRector::class,
            PostIncDecToPreIncDecRector::class,
            FinalizeClassesWithoutChildrenRector::class,
            ChangeAndIfToEarlyReturnRector::class,

            IssetOnPropertyObjectToPropertyExistsRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            RenameVariableToMatchNewTypeRector::class,
            AddLiteralSeparatorToNumberRector::class,
            RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,
            ConsistentPregDelimiterRector::class,
            ChangeOrIfReturnToEarlyReturnRector::class,
            ReturnBinaryAndToEarlyReturnRector::class,
            MakeBoolPropertyRespectIsHasWasMethodNamingRector::class,
            MoveOutMethodCallInsideIfConditionRector::class,
            ReturnArrayClassMethodToYieldRector::class,
            AddArrayParamDocTypeRector::class,
            AddArrayReturnDocTypeRector::class,
            ReturnFluentChainMethodCallToNormalMethodCallRector::class,
            RepeatedLiteralToClassConstantRector::class,
            RenameVariableToMatchNewTypeRector::class,
            ChangeReadOnlyVariableWithDefaultValueToConstantRector::class,
            PrivatizeLocalPropertyToPrivatePropertyRector::class,
            RemoveDelegatingParentCallRector::class,
            ReturnTypeDeclarationRector::class => [
                __DIR__ . '/../Classes/System/Restler/Builder.php',
            ],
            SimplifyIfReturnBoolRector::class => [
                __DIR__ . '/../Classes/System/TYPO3/Cache.php',
            ],
            RemoveDeadInstanceOfRector::class => [
                __DIR__ . '/../Classes/System/Restler/Builder.php',
                __DIR__ . '/../Classes/System/RestApi/RestApiRequest.php'
            ],
            ClosureToArrowFunctionRector::class => [
                __DIR__ . '/../Classes/System/Restler/Builder.php'
            ],
            FunctionArgumentDefaultValueReplacerRector::class => [
                __DIR__ . '/../Classes/System/RestApi/RestApiRequest.php'
            ],
            JsonThrowOnErrorRector::class => [
                __DIR__ . '/../Classes/System/RestApi/RestApiRequest.php',
                __DIR__ . '/../Classes/System/RestApi/RestApiJsonFormat.php',
                __DIR__ . '/../Classes/System/Restler/Format/HalJsonFormat.php'
            ],
            RemoveDoubleUnderscoreInMethodNameRector::class => [
                __DIR__ . '/../Classes/Controller/FeUserAuthenticationController.php',
                __DIR__ . '/../Classes/Controller/ExplorerAuthenticationController.php',
                __DIR__ . '/../Classes/Controller/BeUserAuthenticationController.php'
            ],

        ]
    );

    $rectorConfig->rule(RemoveUnusedPrivatePropertyRector::class);
};
