<?php
namespace Aoe\Restler\System\Restler;
use Aoe\Restler\Configuration\ExtensionConfiguration;
use Luracast\Restler\Restler;

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
 * Configure restler:
 *  - add API- and authentication-class, when online-documentation is enabled (this can be configured via extension-manager)
 *  - add authentication-class, which can be used to check, if FE-user is logged in
 *
 * @package Restler
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var ExtensionConfiguration
     */
    private $extensionConfiguration;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * configure restler:
     *  - add API- and authentication-class, when online documentation is enabled
     *  - add common authentication-class (which can be used for TYPO3-FrontEnd-User-authentication)
     *
     * @param Restler $restler
     * @return void
     */
    public function configureRestler(Restler $restler)
    {
        if ($this->extensionConfiguration->isOnlineDocumentationEnabled()) {
            $restler->addAPIClass('Luracast\\Restler\\Explorer', $this->extensionConfiguration->getPathOfOnlineDocumentation());
            $restler->addAuthenticationClass('Aoe\\Restler\\Controller\\ExplorerAuthenticationController');
        }

        // add authentication-class, which can be used to check, if FE-user is logged in
        $restler->addAuthenticationClass('Aoe\\Restler\\Controller\\FeUserAuthenticationController');
    }
}
