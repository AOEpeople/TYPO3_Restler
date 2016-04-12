<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// add restler-configuration-class
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = 'Aoe\\Restler\\System\\Restler\\Configuration';

/**
 * register cache
 */
if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler'] = array();
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler']['frontend'] =
        '\\TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler']['backend'] = '\\TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_restler']['options'] = array('defaultLifetime' => 0);
}