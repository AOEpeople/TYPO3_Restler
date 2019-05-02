<?php
namespace Aoe\Restler\System\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
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
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use LogicException;

/**
 * @package Restler
 */
class Loader implements SingletonInterface
{
    /**
     * defines, if usage of backend-user is enabled
     *
     * @var boolean
     */
    private $isBackEndUserInitialized = false;
    /**
     * defines, if usage of frontend-user is enabled (this is needed, if the eID-script must determine the frontend-user)
     *
     * @var boolean
     */
    private $isFrontEndUserInitialized = false;
    /**
     * defines, if frontend-rendering is enabled (this is needed, if the eID-script must render some content-elements or RTE-fields)
     *
     * @var boolean
     */
    private $isFrontEndRenderingInitialized = false;

    /**
     * @return BackendUserAuthentication
     * @throws LogicException
     */
    public function getBackEndUser()
    {
        if ($this->isBackEndUserInitialized === false) {
            throw new LogicException('be-user is not initialized - initialize with BE-user with method \'initializeBackendEndUser\'');
        }
        return $GLOBALS['BE_USER'];
    }

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
     * enable the usage of backend-user
     */
    public function initializeBackendEndUser()
    {
        if ($this->isBackEndUserInitialized === true) {
            return;
        }

        $bootstrapObj = Bootstrap::getInstance();
        $bootstrapObj->loadBaseTca(true);
        $bootstrapObj->loadExtTables(true);
        $bootstrapObj->initializeBackendUser();
        $bootstrapObj->initializeBackendAuthentication(true);
        $bootstrapObj->initializeLanguageObject();

        $this->isBackEndUserInitialized = true;
    }

    /**
     * enable the usage of frontend-user
     *
     * @param integer $pageId
     * @param integer $type
     */
    public function initializeFrontEndUser($pageId = 0, $type = 0)
    {
        if (array_key_exists('TSFE', $GLOBALS) && is_object($GLOBALS['TSFE']->fe_user)) {
            // FE-user is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            $this->isFrontEndUserInitialized = true;
        }
        if ($this->isFrontEndUserInitialized === true) {
            return;
        }

        $tsfe = $this->getTsfe($pageId, $type);
        $tsfe->initFEUser();
        $this->isFrontEndUserInitialized = true;
    }

    /**
     * enable the frontend-rendering
     *
     * @param integer $pageId
     * @param integer $type
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function initializeFrontEndRendering($pageId = 0, $type = 0)
    {
        if (array_key_exists('TSFE', $GLOBALS) && is_object($GLOBALS['TSFE']->tmpl)) {
            // FE is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            $this->isFrontEndRenderingInitialized = true;
        }
        if ($this->isFrontEndRenderingInitialized === true) {
            return;
        }

        $GLOBALS['TT'] = new NullTimeTracker();

        if ($this->isFrontEndUserInitialized === false) {
            $this->initializeFrontEndUser($pageId, $type);
        }

        EidUtility::initTCA();

        $tsfe = $this->getTsfe($pageId, $type);
        $tsfe->determineId();
        $tsfe->initTemplate();
        $tsfe->getConfigArray();
        $tsfe->newCObj();
        $tsfe->calculateLinkVars();
        $this->isFrontEndRenderingInitialized = true;
    }

    /**
     * @param integer $pageId
     * @param integer $type
     * @return TypoScriptFrontendController
     */
    private function getTsfe($pageId, $type = 0)
    {
        if ($type > 0) {
            $_GET['type'] = $type;
        }
        if (false === array_key_exists('TSFE', $GLOBALS)) {
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                $pageId,
                $type
            );
        }
        return $GLOBALS['TSFE'];
    }
}
