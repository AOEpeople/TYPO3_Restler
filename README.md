# TYPO3_Restler
This is a TYPO3-Extension, which integrates the restler-Framework (PHP REST-framework to create REST-API's, https://github.com/Luracast/Restler) in TYPO3.

# Installation
1. Install this TYPO3-Extension
2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager; e.g. enable the online documentation of your REST-API)
3. Add Rewrite-Rule to your .htaccess-File, so your REST-API is callable:
   >> RewriteRule ^api/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]
   
   If this is done, than you can call your REST-API via this URL: www.yourDomain.com/api/pathToMyRestApiEndpoints/

4. Add Rewrite-Rule to your .htaccess-File, so the online documentation of your REST-API is callable:
   >> RewriteRule ^api_explorer/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]

   If this is done, than you can call the online-documentation of your REST-API via this URL: www.yourDomain.com/api_explorer/
5. Install the TYPO3-Extension 'restler_examples' (https://github.com/AOEpeople/TYPO3_RestlerExamples), when you want to see/test some REST-API-Examples

# Write your own REST-API
1. Create new PHP-class, which implements this Interface:
   >> Aoe\Restler\System\Restler\ConfigurationInterface
   
2. Add your PHP-class in your TYPO3-Extension (in ext_localconf.php-File):
  >> 
