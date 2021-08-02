.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Developer Manual
====================

Describes how to manage the extension from a developer’s point of view.

Target group: **Developers**


Using TYPO3 Restler in our own extension (aka Exposing your own REST API)
-------------------------------------------------------------------------

In order to offer a seamless integration of Restler [#f1]_ in TYPO3, this extension offers the interface
"Aoe\Restler\System\Restler\ConfigurationInterface".


With classes implementing the "ConfigurationInterface", it is possible to configure Restler:

- Restler configuration can be defined (eg. supported response format)
- API endpoints can be defined
- Authentication classes can be defined (to protect API endpoints against unauthorized access)

Steps:

1. Create new PHP-class, which implements this Interface:

.. code:: php

  Aoe\Restler\System\Restler\ConfigurationInterface

You can use this PHP-class as example

.. code:: php

  Aoe\RestlerExamples\System\Restler\Configuration

Inside your PHP-class, you can configure the Restler framework:

- add API classes to the Restler object
- add Authentication classes to Restler object
- configure all static properties of PHP classes, which belong to the Restler framework

2. Register your PHP-class in your TYPO3-Extension (in ext_localconf.php-File)

Any class that implements the "Aoe\Restler\System\Restler\ConfigurationInterface" interface, must be declared in your
extension "ext_localconf.php" file.

.. code:: php

  // Add the configuration class (in Extension 'restler'):
  // In that configuration class, we can:
  // - configure the cache directory
  // - enable the extbase DI container
  // - enable the online API documentation (if enabled in extension-configuration)
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'][] = 'Aoe\\Restler\\System\\RestlerConfiguration';

This way the following will be achieved:

- each extension can define any number of Restler configuration classes.
- the Restler configuration classes will only be called when an API endpoint is called (has no side-effects on normal calls of TYPO3).

3. Flush the TYPO3 System Cache

Using TYPO3 Restler from PHP libraries
-------------------------------------

If you like to register API classes to the Restler object that are located outside of TYPO3 use the alternative GLOBAL array
to set your configuration class which implements the "Aoe\Restler\System\Restler\ConfigurationInterface" interface:

.. code:: php

    // register API-Controller to TYPO3_Restler
    $GLOBALS['TYPO3_Restler']['restlerConfigurationClasses'][] =
        yourNamespace\Configuration::class;

or you can directly register your REST-controller to an endpoint by using the GLOBAL array:

.. code:: php

    // register API-Controller to Restler
    $GLOBALS['TYPO3_Restler']['addApiClass']['<YOUR_ENDPOINT_PATH>'][] =
        yourNamespace\yourRestController::class;


You just have to make sure that this setting is loaded by auto-loading.
For example (via composer);

.. code:: json

    "autoload": {
        "files": [
            "fileWithGlobalConfigurationRegistration.php"
        ]
    }

Caching of REST-endpoints - via TYPO3-Caching-Framework (experimental)
-------------------------------------

**Notice**: Please note that this feature is experimental and could be removed in further releases.

When you want to use the TYPO3-Caching-Framework to cache the response of your REST-endpoints, you just need to add
this two phpdoc-annotations in your REST-endpoint:

Example:

.. code:: php

    /**
     * The response of this REST-endpoint will be cached by TYPO3-caching-framework, because:
     *  - it's a GET-request/method
     *  - annotation 'restler_typo3cache_expires' (define seconds, after cache is expired; '0' means cache will never expire) is set
     *  - annotation 'restler_typo3cache_tags' (comma-separated list of cache-tags) is set
     *
     * The cache is stored in this TYPO3-tables:
     *  - cache_restler
     *  - cache_restler_tags
     *
     * @url GET my-rest-endpoint-which-should-be-cached
     *
     * @restler_typo3cache_expires 180
     * @restler_typo3cache_tags typo3cache_examples,typo3cache_example_car
     */
    public function myRestEndpointWhichShouldBeCached() {

    }

For details examples please refer to the restler_examples extension.


Exposing Internal APIs (experimental)
-------------------------------------

**Notice**: Please note that this feature is experimental and could be removed in further releases. You can anytime make
internal calls by using an HTTP client (for example Guzzle).

Just as you make HTTP calls to your public endpoints, it is possible to declare internal endpoints which you can
use in a similar way, but using PHP.

You can practically decouple various components by exposing an internal API which is accessible via HTTP just during development.

Example:

.. code:: php

    $this->restApiClient->executeRequest('GET', '/api/rest-api-client/internal_endpoint/cars/1')

For details examples please refer to the restler_examples extension.

Examples
--------

To reduce the quantity of documentation required to use Restler, we provide also an example extension called Restler_examples
available on TER. You can check first there how the endpoints work.

Also please refer to existing documentation regarding the Restler homepage [#f1]_.

.. rubric:: Footnotes

.. [#f1] [RestlerHomepage]: http://www.luracast.com/products/restler
