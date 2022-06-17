<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::PATHS,
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Tests',
            __DIR__ . '/rector.php',
            __DIR__ . '/rector-8_0.php',
        ]
    );

    $containerConfigurator->import(SetList::PHP_81);

    $parameters->set(Option::AUTO_IMPORT_NAMES, false);
    $parameters->set(Option::AUTOLOAD_PATHS, [__DIR__ . '/../Classes']);
    $parameters->set(Option::SKIP, []);

    $services = $containerConfigurator->services();
    $services->set(RemoveUnusedPrivatePropertyRector::class);
};
