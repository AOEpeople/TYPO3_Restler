<?php

namespace Aoe\Restler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2024 AOE GmbH <dev@aoe.com>
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

use Aoe\Restler\System\TYPO3\Loader;
use Luracast\Restler\iAuthenticate;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class checks, if client is allowed to access the requested and protected API-class
 * This class checks, if BE-user is logged in
 */
class BeUserAuthenticationController implements iAuthenticate, SingletonInterface
{
    /**
     * This property defines (when it's set), that this controller should check authentication
     * This property will be automatically set by restler, when in the API-class/controller this
     * is configured (in PHPdoc/annotations)
     *
     * Where do we set this property?
     * When the property should be used, than it must be set inside the PHPdoc-comment of the API-class-method,
     * which handle the API-request
     *
     * Syntax:
     * The PHPdoc-comment must look like this:
     * @class [className] {@[propertyName] [propertyValue]}
     *
     * Example:
     * When this controller should be used for authentication-checks, than the PHPdoc-comment must look like this:
     * @class Aoe\Restler\Controller\BeUserAuthenticationController {@checkAuthentication true}
     */
    public bool $checkAuthentication = false;

    /**
     * Instance of Restler class injected at runtime.
     *
     * @var Restler
     */
    public $restler;

    public function __construct(
        private readonly Loader $typo3Loader
    ) {
        $this->restler = Scope::get('Restler');
    }

    /**
     * This method checks, if client is allowed to access the requested API-class
     *
     * @return boolean
     */
    // phpcs:ignore
    public function __isAllowed()
    {
        if (!$this->checkAuthentication) {
            // this controller is not responsible for the authentication
            return false;
        }

        $this->typo3Loader->initializeBackendUser();

        return $this->typo3Loader->hasActiveBackendUser();
    }

    /**
     * return dummy string, because we DON'T need that for our case (we use NO base-authentification via REST-API)
     *
     * @return string
     * @see \Luracast\Restler\iAuthenticate
     */
    // phpcs:ignore
    public function __getWWWAuthenticateString()
    {
        return '';
    }
}
