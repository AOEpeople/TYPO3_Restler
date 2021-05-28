<?php
namespace Aoe\Restler\System\TYPO3;

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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
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
     * Checks if a backend user is logged in.
     *
     * @return bool
     */
    public function hasActiveBackendUser()
    {
        return ($GLOBALS['BE_USER'] ?? null) instanceof BackendUserAuthentication &&
            $GLOBALS['BE_USER']->user['uid'] > 0;
    }

    /**
     * @return BackendUserAuthentication
     * @throws LogicException
     */
    public function getBackendUser()
    {
        if ($this->hasActiveBackendUser() === false) {
            throw new LogicException('be-user is not initialized - initialize with BE-user with method \'initializeBackendUser\'');
        }
        return $GLOBALS['BE_USER'];
    }

    /**
     * Checks if a frontend user is logged in and the session is active.
     *
     * @return bool
     */
    public function hasActiveFrontendUser()
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController &&
            $GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication &&
            isset($GLOBALS['TSFE']->fe_user->user['uid']);
    }

    /**
     * @return FrontendUserAuthentication
     * @throws LogicException
     */
    public function getFrontendUser()
    {
        if ($this->hasActiveFrontendUser() === false) {
            throw new LogicException('fe-user is not initialized');
        }
        return $GLOBALS['TSFE']->fe_user;
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
        if ($this->isFrontendInitialized()) {
            // FE is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            $this->isFrontendRenderingInitialized = true;
        }
        if ($this->isFrontendRenderingInitialized) {
            return;
        }

        $this->getTypoScriptFrontendController($pageId, $type);

        $this->isFrontendRenderingInitialized = true;
    }

    /**
     * Checks if the frontend is initialized.
     *
     * @return bool
     */
    protected function isFrontendInitialized()
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController &&
            $GLOBALS['TSFE']->tmpl instanceof TemplateService;
    }

    /**
     * @param integer $pageId
     * @param integer $type
     * @return TypoScriptFrontendController
     */
    private function getTypoScriptFrontendController($pageId = 0, $type = 0)
    {
        if ($this->isFrontendInitialized()) {
            return $GLOBALS['TSFE'];
        }

        $context = GeneralUtility::makeInstance(Context::class);
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($pageId);
        $pageArguments = new PageArguments($pageId, $type, [], [], []);
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $context,
            $site,
            $site->getDefaultLanguage(),
            $pageArguments
        );
        $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class, $context);
        $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance(TemplateService::class);

        return $GLOBALS['TSFE'];
    }
}
