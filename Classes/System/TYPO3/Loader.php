<?php
namespace Aoe\Restler\System\TYPO3;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

// we must load some PHP/TYPO3-classes manually, because at this point, TYPO3 (and it's auto-loading) is not initialized
require_once __DIR__ . '/../../../../../../typo3/sysext/core/Classes/Core/Bootstrap.php';
require_once __DIR__ . '/../../../../../../typo3/sysext/core/Classes/SingletonInterface.php';

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
 * @package Restler
 *
 * @codeCoverageIgnore
 */
class Loader implements SingletonInterface
{
    /**
     * defines, if frontend-rendering is enabled (this is needed, if the eID-script must render some content-elements or RTE-fields)
     *
     * @var boolean
     */
    private $isFrontEndRenderingInitialized = false;
    /**
     * defines, if usage of frontend-user is enabled (this is needed, if the eID-script must determine the frontend-user)
     *
     * @var boolean
     */
    private $isFrontEndUserInitialized = false;

    /**
     * enable the frontend-rendering
     * This is used e.g. while rendering data of productCatalogue
     */
    public function initializeFrontEndRendering()
    {
        if ($this->isFrontEndRenderingInitialized === false) {
            EidUtility::initTCA();

            $tsfe = $this->getTsfe();
            $tsfe->determineId();
            $tsfe->initTemplate();
            $tsfe->getConfigArray();
            $this->isFrontEndRenderingInitialized = true;
        }
    }

    /**
     * enable the usage of frontend-user
     */
    public function initializeFrontEndUser()
    {
        if ($this->isFrontEndUserInitialized === false) {
            // Initialize FE User
            $tsfe = $this->getTsfe();
            $tsfe->initFEUser();
            $this->isFrontEndUserInitialized = true;
        }
    }

    /**
     * enable the usage of TYPO3 - start TYPO3 by calling the TYPO3-bootstrap
     */
    public function initializeTypo3()
    {
        // we must define this constant, otherwise some TYPO3-extensions will not work!
        define('TYPO3_MODE', 'FE');

        Bootstrap::getInstance()
            ->baseSetup('typo3conf/ext/restler/Scripts/') // web-server has called this PHP-script 'restler/Scripts/dispatch.php'
            ->startOutputBuffering()
            ->loadConfigurationAndInitialize()
            ->loadTypo3LoadedExtAndExtLocalconf()
            ->applyAdditionalConfigurationSettings()
            ->initializeTypo3DbGlobal();

        // create timeTracker-object (TYPO3 needs that)
        $GLOBALS['TT'] = new NullTimeTracker();
    }

    /**
     * @param integer $pageId
     * @return TypoScriptFrontendController
     */
    private function getTsfe($pageId = 2)
    {
        if (false === array_key_exists('TSFE', $GLOBALS)) {
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
                $GLOBALS['TYPO3_CONF_VARS'],
                $pageId,
                0
            );
        }
        return $GLOBALS['TSFE'];
    }
}
