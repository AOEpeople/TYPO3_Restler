<?php
use Doctrine\Common\Annotations\AnnotationReader;
use Aoe\Restler\System\Restler\Configuration;
use Aoe\Restler\System\TYPO3\RestlerEnhancer;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

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
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'])) {
    // @TODO TYPO3 v12:
    // In TYPO3 v12, it's NOT allowed, that the cache-configuration contains the prefix 'cache_', so we must change
    // the cache-configuration-name from 'cache_restler' to just 'restler'. We didn't changed this for TYPO3 v10/v11,
    // because 3rd-party-extensions maybe override the current cache-configuration-name 'cache_restler'.

    // only configure cache, when cache is not already configured (e.g. by any other extension which base on this extension)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'] = [
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
