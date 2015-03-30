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

// initialize TYPO3 (after that, we can use the autoLoading of TYPO3)
require_once __DIR__ . '/../../../../typo3conf/ext/restler/Classes/System/TYPO3/Loader.php';
$typo3Loader = new Aoe\Restler\System\TYPO3\Loader();
$typo3Loader->initializeTypo3();

// dispatch the API-call
$objectManager = new TYPO3\CMS\Extbase\Object\ObjectManager();
$dispatcher = $objectManager->get('Aoe\\Restler\\System\\Dispatcher');
$dispatcher->dispatch();
