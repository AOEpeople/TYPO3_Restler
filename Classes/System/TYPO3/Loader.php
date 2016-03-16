<?php
namespace Aoe\Restler\System\TYPO3;

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

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use LogicException;

// @codingStandardsIgnoreStart
// we must load some PHP/TYPO3-classes manually, because at this point, TYPO3 (and it's auto-loading) is not initialized
require_once __DIR__ . '/../../../../../../typo3/sysext/core/Classes/Core/ApplicationContext.php';
require_once __DIR__ . '/../../../../../../typo3/sysext/core/Classes/Core/Bootstrap.php';
require_once __DIR__ . '/../../../../../../typo3/sysext/core/Classes/SingletonInterface.php';
// @codingStandardsIgnoreEnd

/**
 * @package Restler
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
     * @return FrontendUserAuthentication
     * @throws LogicException
     */
    public function getFrontEndUser()
    {
        if ($this->isFrontEndUserInitialized === false) {
            throw new LogicException('fe-user is not initialized - initialize with FE-user with method \'initializeFrontEndUser\'');
        }
        return $GLOBALS['TSFE']->fe_user;
    }

    /**
     * enable the frontend-rendering
     *
     * @param integer $pageId
     */
    public function initializeFrontEndRendering($pageId = 0)
    {
        if ($this->isFrontEndRenderingInitialized === true) {
            return;
        }

        if ($this->isFrontEndUserInitialized === false) {
            $this->initializeFrontEndUser($pageId);
        }

        EidUtility::initTCA();

        $tsfe = $this->getTsfe($pageId);
        $tsfe->determineId();
        $tsfe->initTemplate();
        $tsfe->getConfigArray();
        $this->isFrontEndRenderingInitialized = true;
    }

    /**
     * enable the usage of frontend-user
     *
     * @param integer $pageId
     */
    public function initializeFrontEndUser($pageId = 0)
    {
        if ($this->isFrontEndUserInitialized === true) {
            return;
        }

        $tsfe = $this->getTsfe($pageId);
        $tsfe->initFEUser();
        $this->isFrontEndUserInitialized = true;
    }

    /**
     * enable the usage of TYPO3 - start TYPO3 by calling the TYPO3-bootstrap
     */
    public function initializeTypo3()
    {
        // we must define this constant, otherwise some TYPO3-extensions will not work!
        define('TYPO3_MODE', 'FE');
        // we define this constant, so that any TYPO3-Extension can check, if the REST-API is running
        define('REST_API_IS_RUNNING', true);

        // configure TYPO3 (e.g. paths, variables and classLoader)
        $bootstrapObj = Bootstrap::getInstance();
        if (true === method_exists($bootstrapObj, 'applyAdditionalConfigurationSettings')) {
            // it seams to be TYPO3 6.2 (LTS)
            $bootstrapObj->baseSetup('typo3conf/ext/restler/Scripts/'); // server has called script 'restler/Scripts/restler_dispatch.php'
            $bootstrapObj->startOutputBuffering();
            $bootstrapObj->loadConfigurationAndInitialize();

            // configure TYPO3 (load ext_localconf.php-files of TYPO3-extensions)
            $this->getExtensionManagementUtility()->loadExtLocalconf();

            // configure TYPO3 (Database and further settings)
            $bootstrapObj->applyAdditionalConfigurationSettings();
            $bootstrapObj->initializeTypo3DbGlobal();
        } else {
            // it seams to be TYPO3 7.6 (LTS)
            $classLoader = require __DIR__ . '/../../../../../../typo3_src/vendor/autoload.php';
            $bootstrapObj->initializeClassLoader($classLoader);
            $bootstrapObj->baseSetup('typo3conf/ext/restler/Scripts/'); // server has called script 'restler/Scripts/restler_dispatch.php'
            $bootstrapObj->startOutputBuffering();
            $bootstrapObj->loadConfigurationAndInitialize();

            // configure TYPO3 (load ext_localconf.php-files of TYPO3-extensions)
            $this->getExtensionManagementUtility()->loadExtLocalconf();

            // configure TYPO3 (Database and further settings)
            $bootstrapObj->setFinalCachingFrameworkCacheConfiguration();
            $bootstrapObj->defineLoggingAndExceptionConstants();
            $bootstrapObj->unsetReservedGlobalVariables();
            $bootstrapObj->initializeTypo3DbGlobal();
        }

        // create timeTracker-object (TYPO3 needs that)
        $GLOBALS['TT'] = new NullTimeTracker();
    }

    /**
     * create instance of ExtensionManagementUtility
     *
     * Attention:
     * Don't use the dependency-injection of TYPO3, because at this time (where we initialize TYPO3), the DI is not available!
     *
     * @return ExtensionManagementUtility
     */
    private function getExtensionManagementUtility()
    {
        $cacheManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $extensionConfiguration = GeneralUtility::makeInstance('Aoe\\Restler\\Configuration\\ExtensionConfiguration');
        return new ExtensionManagementUtility($cacheManager, $extensionConfiguration);
    }

    /**
     * @param integer $pageId
     * @return TypoScriptFrontendController
     */
    private function getTsfe($pageId)
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
