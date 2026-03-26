// Add the configuration class (in Extension 'restler'):
// In that configuration class, we can:
// - configure the cache directory
// - enable the extbase DI container
// - enable the online API documentation (if enabled in extension-configuration)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = 'Aoe\\Restler\\System\\RestlerConfiguration';
