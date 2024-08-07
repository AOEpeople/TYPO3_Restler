<?php
use Doctrine\Common\Annotations\AnnotationReader;
use Aoe\Restler\System\Restler\Configuration;
use Aoe\Restler\System\TYPO3\RestlerEnhancer;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;

defined('TYPO3') or die();

// base restler annotations
$restlerAnnotations = ['url',
    'access',
    'smart-auto-routing',
    'class',
    'cache',
    'expires',
    'throttle',
    'status',
    'header',
    'param',
    'throws',
    'return',
    'var',
    'format',
    'view',
    'errorView'];

foreach ($restlerAnnotations as $ignoreAnnotation) {
    AnnotationReader::addGlobalIgnoredName($ignoreAnnotation);
}

// restler plugin annotations
AnnotationReader::addGlobalIgnoredName('restler_typo3cache_expires');
AnnotationReader::addGlobalIgnoredName('restler_typo3cache_tags');

// add restler-configuration-class
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = Configuration::class;

// add restler page routing
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']['Restler'] = RestlerEnhancer::class;

/**
 * register cache which can cache response of REST-endpoints
 */
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['restler'])) {
    // only configure cache, when cache is not already configured (e.g. by any other extension which base on this extension)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['restler'] = [
        'frontend' => VariableFrontend::class,
        'backend' => Typo3DatabaseBackend::class,
        'options' => ['defaultLifetime' => 0]
    ];
}

/**
 * register cache which will be used from restler (to e.g. cache the routes.php)
 */
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_restler_cache'])) {
    // only configure cache, when cache is not already configured (e.g. by any other extension which base on this extension)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_restler_cache'] = [
        'backend' => SimpleFileBackend::class,
        'groups' => ['system']
    ];
}
