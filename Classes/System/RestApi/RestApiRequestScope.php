<?php

namespace Aoe\Restler\System\RestApi;

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
    private $originalRestApiRequest;
    /**
     * This array contains all instanciated REST-API-authentication-objects, which
     * where already initialized before we call the REST-API-request via PHP
     *
     * @var array
     */
    private $originalRestApiAuthenticationObjects = [];

    /**
     * @return Restler
     */
    public function getOriginalRestApiRequest()
    {
        if (isset($this->originalRestApiRequest)) {
            return $this->originalRestApiRequest;
        }
        return static::get('Restler');
    }

    /**
     * Override (the stored) restler-object, because this restler-object 'defines' the REST-API-request, which we want to call
     *
     * @param RestApiRequest $restApiRequest
     */
    public function overrideOriginalRestApiRequest(RestApiRequest $restApiRequest)
    {
        static::set('Restler', $restApiRequest);
    }

    /**
     * Remove all initialized REST-API-authentication-objects, which are currently instanciated. We must do this, because the
     * REST-API-authentication-objects can depend on the called REST-API-endpoint, because they can be instanciated with different
     * 'configurations', which depends on the called REST-API-endpoint. The REST-API-endpoint can contain 'configurations' for the
     * REST-API-authentication-object like this:
     * @class [authentication-class] {@[property-of-authentication-class] [property-value]}
     * @class Aoe\MyRestApiExtension\Controller\MyAuthenticationController {@checkAuthentication true}
     */
    public function removeRestApiAuthenticationObjects()
    {
        foreach ($this->getOriginalRestApiRequest()->_authClasses as $className) {
            if (array_key_exists($className, static::$instances)) {
                unset(static::$instances[$className]);
            }
        }
    }

    /**
     * Restore (the overridden) restler-object
     */
    public function restoreOriginalRestApiRequest()
    {
        static::set('Restler', $this->originalRestApiRequest);
    }

    /**
     * Restore all initialized REST-API-authentication-objects, which
     * were already initialized before we call the REST-API-request via PHP
     */
    public function restoreOriginalRestApiAuthenticationObjects()
    {
        $this->removeRestApiAuthenticationObjects();

        foreach ($this->originalRestApiAuthenticationObjects as $className => $object) {
            static::$instances[$className] = $object;
        }
    }

    /**
     * store (the original) restler-object
     *
     * @param Restler $originalRestApiRequest optional, default is null (normally, the object already exists in the 'Scope-Repository')
     */
    public function storeOriginalRestApiRequest(Restler $originalRestApiRequest = null)
    {
        if ($originalRestApiRequest instanceof Restler) {
            static::set('Restler', $originalRestApiRequest);
        }
        $this->originalRestApiRequest = static::get('Restler');
    }

    /**
     * store all REST-API-authentication-objects, which where already initialized before we call the REST-API-request via PHP
     */
    public function storeOriginalRestApiAuthenticationObjects()
    {
        foreach ($this->getOriginalRestApiRequest()->_authClasses as $className) {
            if (array_key_exists($className, static::$instances)) {
                $this->originalRestApiAuthenticationObjects[$className] = static::$instances[$className];
            }
        }
    }
}
