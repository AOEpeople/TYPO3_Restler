.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Configuration Options
=====================

Target group: **Developers**

You can make various configurations directly from the Extension Manager in TYPO3.

ProductionContext [basic.productionContext]
-------------------------------------------

When productionContext is not set, than Restler will:
- show debug information
- not cache the routes (will parse the API-classes every time to map it to the URL)

Refresh cache [basic.refreshCache]
----------------------------------

You can use this configuration to rebuild cache every time in production mode.
When in production mode, a cache file for the routes will be written in the cache directory by default.

TYPO3-Extensions with ext_localconf.php files [basic.extensionsWithRequiredExtLocalConfFiles]
---------------------------------------------------------------------------------------------

Define TYPO3 Extensions from where the ext_localconf.php file must be loaded.

You can use:

- comma separated list of TYPO3-Extensions of the extensions which use Restler
- leave this field empty if all ext_localconf.php files of all extensions should be loaded (which can cost a lot of performance)


Enable online documentation [basic.enableOnlineDocumentation]
-------------------------------------------------------------

Defines whether to enable the online documentation of the REST-API.


Path to online documentation [basic.pathToOnlineDocumentation]
--------------------------------------------------------------

Defines the path to the online documentation of the REST-API (e.g. www.example.com/<pathToOnlineDocumentation>/)





