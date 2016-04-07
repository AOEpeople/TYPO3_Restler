.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Installation Guide
====================

Describes how to manage the extension from a developer’s point of view.

Target group: **Developers**


Installation
------------

1. Install the extension from TER (sources are available on `our GitHub repository <https://github.com/AOEpeople/TYPO3_Restler>`_).

In the context of a fully Composer setup, add in your root composer.json the following lines before doing a ```composer update```.

::

	"require": {
		...
		"aoe/restler": "1.*"
	}
	"autoload": {
		"psr-0": {
			"Luracast\\Restler\\": "vendor/luracast/restler/vendor"
		}
	},

2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager; e.g. enable the online documentation of your REST-API). See the "Screenshots" section as well.

3. (Optional, but recommended) Install the TYPO3 Extension "TYPO3 Restler Examples".

4. Make the .htaccess changes described below:

The ".htaccess" file needs to be changed in order to make the REST API available.

.. parsed-literal::

  # Allow access to example.com/api for normal REST calls
  RewriteRule ^api/(.*)$ typo3conf/ext/restler/Scripts/restler_dispatch.php [NC,QSA,L]

  # Allow access to example.com/api_explorer for the online documentation of your API.
  # You may want to restrict access to this URL.
  RewriteRule ^api_explorer/(.*)$ typo3conf/ext/restler/Scripts/restler_dispatch.php [NC,QSA,L]

When this is done, than you can call the online documentation of your REST API via this URL:

..

  www.example.com/api_explorer/

You also can call your REST API via this URL:

..

    www.example.com/api/path-to-my-rest-api-endpoints/.


