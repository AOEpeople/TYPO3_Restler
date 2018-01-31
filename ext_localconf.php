<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// add restler-configuration-class
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = 'Aoe\\Restler\\System\\Restler\\Configuration';

/**
 * register cache which can cache response of REST-endpoints
 */
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'])) {
    // only configure cache, when cache is not already configured (e.g. by any other extension which base on this extension)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => ['defaultLifetime' => 0]
    ];
}

/**
 * register cache which will be used from restler (to e.g. cache the routes.php)
 */
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_restler_cache'])) {
    // only configure cache, when cache is not already configured (e.g. by any other extension which base on this extension)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_restler_cache'] = [
        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
        'groups' => ['system']
    ];
}

// Register request handler for API
\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->registerRequestHandlerImplementation(\Aoe\Restler\Http\RestRequestHandler::class);
