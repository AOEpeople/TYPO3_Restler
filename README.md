# TYPO3_Restler
This is a TYPO3-Extension, which integrates the restler-Framework (PHP REST-framework to create REST-API's, https://github.com/Luracast/Restler) in TYPO3.

# Installation
1. Install this TYPO3-Extension
2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager; e.g. enable the online documentation of your REST-API)
3. Add Rewrite-Rule to your .htaccess-File, so your REST-API is callable:
   >> RewriteRule ^api/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]

   If this is done, than you can call your REST-API via this URL: www.your-domain.com/api/path-to-my-rest-api-endpoints/

4. Add Rewrite-Rule to your .htaccess-File, so the online documentation of your REST-API is callable:
   >> RewriteRule ^api_explorer/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]

   If this is done, than you can call the online-documentation of your REST-API via this URL: www.your-domain.com/api_explorer/
5. Install the TYPO3-Extension 'restler_examples' (https://github.com/AOEpeople/TYPO3_RestlerExamples), when you want to see/test some REST-API-Examples

# Write your own REST-API
1. Create new PHP-class, which implements this Interface:
   >> Aoe\Restler\System\Restler\ConfigurationInterface

   You can use this PHP-class as example
   >> Aoe\\RestlerExamples\\System\\Restler\\Configuration

   Inside your PHP-class, you can configure the restler-framework:
    - add API-classes to restler-object
    - add Authentication-classes to restler-object
    - configure all static properties of PHP-classes, which belong to the restler-framework

2. Register your PHP-class in your TYPO3-Extension (in ext_localconf.php-File):
  >> $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = 'yourPhpClass';

3. Flush the TYPO3-System-Cache
