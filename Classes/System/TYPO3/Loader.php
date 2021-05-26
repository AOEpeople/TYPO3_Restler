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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;
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
    private $isBackendUserInitialized = false;
    /**
     * defines, if usage of frontend-user is enabled (this is needed, if the eID-script must determine the frontend-user)
     *
     * @var boolean
     */
    private $isFrontendUserInitialized = false;
    /**
     * defines, if frontend-rendering is enabled (this is needed, if the eID-script must render some content-elements or RTE-fields)
     *
     * @var boolean
     */
    private $isFrontendRenderingInitialized = false;

    /**
     * @return BackendUserAuthentication
     * @throws LogicException
     */
    public function getBackendUser()
    {
        if ($this->isBackendUserInitialized === false) {
            throw new LogicException('be-user is not initialized - initialize with BE-user with method \'initializeBackendUser\'');
        }
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return FrontendUserAuthentication
     * @throws LogicException
     */
    public function getFrontendUser()
    {
        if ($this->isFrontendUserInitialized === false) {
            throw new LogicException('fe-user is not initialized - initialize with FE-user with method \'initializeFrontendUser\'');
        }
        return $GLOBALS['TSFE']->fe_user;
    }

    /**
     * enable the usage of backend-user
     */
    public function initializeBackendUser()
    {
        if (!class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
            if ($this->isBackendUserInitialized === true) {
                return;
            }

            $bootstrapObj = Bootstrap::getInstance();
            $bootstrapObj->loadExtensionTables(true);
            $bootstrapObj->initializeBackendUser();
            $bootstrapObj->initializeBackendAuthentication(true);
            $bootstrapObj->initializeLanguageObject();
        }

        $this->isBackendUserInitialized = true;
    }

    /**
     * enable the usage of frontend-user
     *
     * @param integer $pageId
     * @param integer $type
     */
    public function initializeFrontendUser($pageId = 0, $type = 0)
    {
        if (!class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
            if (array_key_exists('TSFE', $GLOBALS) && is_object($GLOBALS['TSFE']->fe_user)) {
                // FE-user is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
                $this->isFrontendUserInitialized = true;
            }
            if ($this->isFrontendUserInitialized === true) {
                return;
            }

            $tsfe = $this->getTsfe($pageId, $type);
            $tsfe->initFEUser();
        }

        $this->isFrontendUserInitialized = true;
    }

    /**
     * enable the frontend-rendering
     *
     * @param integer $pageId
     * @param integer $type
     *
     * @return void
     */
    public function initializeFrontendRendering($pageId = 0, $type = 0)
    {
        if (array_key_exists('TSFE', $GLOBALS) && is_object($GLOBALS['TSFE']->tmpl)) {
            // FE is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            $this->isFrontendRenderingInitialized = true;
        }
        if ($this->isFrontendRenderingInitialized === true) {
            return;
        }

        if (class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
            $this->getTsfe($pageId, $type);
        } else {
            $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class);

            if ($this->isFrontendUserInitialized === false) {
                $this->initializeFrontendUser($pageId, $type);
            }

            EidUtility::initTCA();

            $tsfe = $this->getTsfe($pageId, $type);
            $tsfe->determineId();
            $tsfe->initTemplate();
            $tsfe->getConfigArray();
            $tsfe->newCObj();
            $tsfe->calculateLinkVars();
        }

        $this->isFrontendRenderingInitialized = true;
    }

    /**
     * @param integer $pageId
     * @param integer $type
     * @return TypoScriptFrontendController
     */
    private function getTsfe($pageId = 0, $type = 0)
    {
        if (false === array_key_exists('TSFE', $GLOBALS) && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            return $GLOBALS['TSFE'];
        }

        if (class_exists(\TYPO3\CMS\Core\Site\Entity\NullSite::class)) {
            $context = GeneralUtility::makeInstance(Context::class);
            $nullSite = new NullSite();
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $context,
                $nullSite,
                $nullSite->getDefaultLanguage()
            );
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class, $context);
            $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        } else {
            if ($type > 0) {
                $_GET['type'] = $type;
            }
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                $pageId,
                $type
            );
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
            $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        }

        return $GLOBALS['TSFE'];
    }
}
