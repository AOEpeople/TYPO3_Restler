<?php
namespace Aoe\Restler\System\Restler;

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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\Controller\BeUserAuthenticationController;
use Aoe\Restler\Controller\ExplorerAuthenticationController;
use Aoe\Restler\Controller\FeUserAuthenticationController;
use Luracast\Restler\Explorer\v2\Explorer;
use Luracast\Restler\Restler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Configure restler:
 *  - add API- and authentication-class, when online-documentation is enabled
 *    (this can be configured via extension-manager)
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
    public function __construct(ExtensionConfiguration $extensionConfiguration = null)
    {
        $this->extensionConfiguration = $extensionConfiguration ?? GeneralUtility::makeInstance(ObjectManager::class)
                ->get(ExtensionConfiguration::class);
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
            $restler->addAPIClass(Explorer::class, $this->extensionConfiguration->getPathOfOnlineDocumentation());
            $restler->addAuthenticationClass(ExplorerAuthenticationController::class);
        }

        // add authentication-class, which can be used to check, if BE-user is logged in
        $restler->addAuthenticationClass(BeUserAuthenticationController::class);

        // add authentication-class, which can be used to check, if FE-user is logged in
        $restler->addAuthenticationClass(FeUserAuthenticationController::class);
    }
}
