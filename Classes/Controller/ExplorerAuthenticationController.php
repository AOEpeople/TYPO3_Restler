<?php
namespace Aoe\Restler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 AOE GmbH <dev@aoe.com>
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

use Luracast\Restler\Explorer\v2\Explorer;
use Luracast\Restler\iAuthenticate;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

/**
 * This class checks, if client is allowed to access the API-online-documentation
 * This class is very simple, it doesn't check any API-keys! If you have the need to
 * support (different) API-keys, you must write your own authentication-controller!
 * @see http://restler3.luracast.com/examples/_010_access_control/readme.html
 */
class ExplorerAuthenticationController implements iAuthenticate
{
    /**
     * Instance of Restler class injected at runtime.
     *
     * @var Restler
     */
    public $restler;

    /**
     * initialize controller
     */
    public function __construct()
    {
        $this->restler = Scope::get('Restler');
    }

    /**
     * This method checks, if client is allowed to access the API-online-documentation
     *
     * @return boolean
     */
    public function __isAllowed()
    {
        if ($this->restler->apiMethodInfo->className !== Explorer::class) {
            // this controller is not responsible for the authentication
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @see \Luracast\Restler\iAuthenticate
     */
    public function __getWWWAuthenticateString()
    {
        return 'Query name="api_key"';
    }
}
