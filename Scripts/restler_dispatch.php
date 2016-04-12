<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * fix GET-params:
 * It can happen, that the apache (through apache-redirect-config to redirect to this script) set this variables:
 *  - $_SERVER['SCRIPT_URL'] is:            '/api/products/4711'
 *  - GET-params are:                       array('products/4711' => '')
 *
 * In this case, we modify the GET-params so that the SCRIPT_URL (without '/api/' is NOT configured as GET-param).
 * The result is that the GET-params are:   array()
 */
if (array_key_exists('SCRIPT_URL', $_SERVER) &&
    array_key_exists(substr($_SERVER['SCRIPT_URL'], strlen('/api/')), $_GET) &&
    $_GET[substr($_SERVER['SCRIPT_URL'], strlen('/api/'))] === '') {
    unset($_GET[substr($_SERVER['SCRIPT_URL'], strlen('/api/'))]);
}

// initialize TYPO3 (after that, we can use the autoLoading of TYPO3)
require_once __DIR__ . '/../../../../typo3conf/ext/restler/Classes/System/TYPO3/Loader.php';
$typo3Loader = new Aoe\Restler\System\TYPO3\Loader();
$typo3Loader->initializeTypo3();

// dispatch the API-call
$objectManager = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
/** @var Aoe\Restler\System\Dispatcher $dispatcher */
$dispatcher = $objectManager->get('Aoe\\Restler\\System\\Dispatcher');
$dispatcher->dispatch();
