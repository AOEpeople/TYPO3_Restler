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

In the context of a fully Composer setup, add in your root composer.json the following line before doing a ```composer update```.

::

	"require": {
		...
		"aoe/restler": "2.*"
	},

Because we are using a forked version of Restler (in order to be able to tag a stable version), you also need to add the following to your composer.json file.

::

	    "repositories": [
	        {
	            "type": "vcs",
	            "url": "https://github.com/AOEpeople/Restler.git"
	        }
	    ],

2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager; e.g. enable the online documentation of your REST-API). See the "Screenshots" section as well.

3. (Optional, but recommended) Install the TYPO3 Extension "TYPO3 Restler Examples".

4. Make the .htaccess changes described below:

The ".htaccess" file needs to be changed in order to make the REST API available.

.. parsed-literal::
    # The api_explorer/.* MUST not hit TYPO3 (index.php) otherwise the requests will fail.
    # This / (target) is only to prevent this to happen.
    RewriteRule ^api_explorer/.*$ / [NC,QSA,L]

For Nginx use following rule

.. parsed-literal::

    location ~^/api_explorer/ {
            rewrite ^/api_explorer/(.*)$ / last;
    }

When this is done, than you can call the online documentation of your REST API via this URL:

..

  www.example.com/api_explorer/

You also can call your REST API via this URL:

..

    www.example.com/api/path-to-my-rest-api-endpoints/.


