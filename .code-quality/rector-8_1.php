<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Tests',
            __DIR__ . '/rector-8_1.php',
        ]
    );

    $rectorConfig->import(SetList::PHP_82);

    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);

    $rectorConfig->importNames(false);
    $rectorConfig->autoloadPaths([__DIR__ . '/../Classes']);
    $rectorConfig->cacheDirectory('.cache/rector/upgrade_8-1/');
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
        ]
    );

    $rectorConfig->rule(RemoveUnusedPrivatePropertyRector::class);
};
