.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Developer Manual
====================

Describes how to manage the extension from a developer’s point of
view.

Target group: **Developers**


Installation
------------

1. Install the extension from TER or from `our GitHub repository <https://github.com/AOEpeople/TYPO3_Restler>`_.

2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager; e.g. enable the online documentation of your REST-API). See the "Screenshots" section as well.

3. (Optional, but recommended) Install the TYPO3 Extension "TYPO3 Restler Examples".

4. Make the .htaccess changes described below:

The ".htaccess" file needs to be changed in order to make the REST API available.

.. parsed-literal::

  # Allow access to example.com/api for normal REST calls
  RewriteRule ^api/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]

  # Allow access to example.com/api_explorer for the online documentation of your API.
  # You may want to restrict access to this URL.
  RewriteRule ^api_explorer/(.*)$ typo3conf/ext/restler/Scripts/dispatch.php [NC,QSA,L]

When this is done, than you can call the online documentation of your REST API via this URL:

..

  www.example.com/api_explorer/

You also can call your REST API via this URL:

..

    www.example.com/api/path-to-my-rest-api-endpoints/.


Using TYPO3 Restler in our own extension (aka Writing your own REST API)
------------------------------------------------------------------------

In order to offer a seamless integration of Restler in TYPO3, this extension offers the interface
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

The API call sequence briefly explained
---------------------------------------

@TODO



