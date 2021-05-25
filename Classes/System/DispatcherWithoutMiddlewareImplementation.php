<?php

namespace Aoe\Restler\System;

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

use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @package Restler
 */
class DispatcherWithoutMiddlewareImplementation
{
    /**
     * @var RestlerBuilder
     */
    private $restlerBuilder;

    /**
     * @param RestlerBuilder $restlerBuilder
     */
    public function __construct(RestlerBuilder $restlerBuilder = null)
    {
        $this->restlerBuilder = $restlerBuilder ?? GeneralUtility::makeInstance(ObjectManager::class)->get(RestlerBuilder::class);
    }

    /**
     * dispatch the REST-API-request
     */
    public function dispatch()
    {
        $restlerObj = $this->restlerBuilder->build();
        $restlerObj->handle();
    }
}
