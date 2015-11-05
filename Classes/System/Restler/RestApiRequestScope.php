<?php
namespace Aoe\Restler\System\Restler;

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

use Luracast\Restler\Restler;
use Luracast\Restler\Scope;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * We must override the Scope-class from restler. Otherwise we can't override the 'original' Restler-object (which represents
 * the REST-API-Request) when we want to execute (multiple) REST-API-requests via the class Aoe\Restler\System\Restler\RestApiRequest
 */
class RestApiRequestScope extends Scope implements SingletonInterface
{
    /**
     * @var Scope
     */
    private $originalRestApiRequestObj;

    /**
     * @return Restler
     */
    public function getOriginalRestApiRequestObj()
    {
        if (isset($this->originalRestApiRequestObj)) {
            return $this->originalRestApiRequestObj;
        }
        return static::get('Restler');
    }

    /**
     * Override (the stored) restler-object, because this restler-object 'defines' the REST-API-request, which we want to call
     *
     * @param RestApiRequest $restApiRequest
     */
    public function overrideOriginalRestApiRequestObj(RestApiRequest $restApiRequest)
    {
        static::set('Restler', $restApiRequest);
    }

    /**
     * Restore (the overridden) restler-object
     */
    public function restoreOriginalRestApiRequestObj()
    {
        static::set('Restler', $this->originalRestApiRequestObj);
    }

    /**
     * store (the original) restler-object
     */
    public function storeOriginalRestApiRequestObj()
    {
        $this->originalRestApiRequestObj = static::get('Restler');
    }
}
