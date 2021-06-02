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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Http\RequestHandler;
use TYPO3\CMS\Frontend\Middleware\BackendUserAuthenticator;
use TYPO3\CMS\Frontend\Middleware\FrontendUserAuthenticator;
use TYPO3\CMS\Frontend\Middleware\PrepareTypoScriptFrontendRendering;
use TYPO3\CMS\Frontend\Middleware\TypoScriptFrontendInitialization;
use LogicException;

/**
 * @package Restler
 */
class Loader implements SingletonInterface
{
    /**
     * @var BackendUserAuthenticator
     */
    private $backendUserAuthenticator;

    /**
     * @var FrontendUserAuthenticator
     */
    private $frontendUserAuthenticator;

    /**
     * @var MockedRequestHandler
     */
    private $mockedRequestHandler;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var TimeTracker
     */
    protected $timeTracker;

    /**
     * @var TypoScriptFrontendInitialization
     */
    private $typoScriptFrontendInitialization;

    /**
     * @param BackendUserAuthenticator         $backendUserAuthenticator
     * @param FrontendUserAuthenticator        $frontendUserAuthenticator
     * @param MockedRequestHandler             $mockedRequestHandler
     * @param RequestHandler                   $requestHandler
     * @param TimeTracker                      $timeTracker
     * @param TypoScriptFrontendInitialization $typoScriptFrontendInitialization
     */
    public function __construct(
        BackendUserAuthenticator $backendUserAuthenticator,
        FrontendUserAuthenticator $frontendUserAuthenticator,
        MockedRequestHandler $mockedRequestHandler,
        RequestHandler $requestHandler,
        TimeTracker $timeTracker,
        TypoScriptFrontendInitialization $typoScriptFrontendInitialization
    ) {
        $this->backendUserAuthenticator = $backendUserAuthenticator;
        $this->frontendUserAuthenticator = $frontendUserAuthenticator;
        $this->mockedRequestHandler = $mockedRequestHandler;
        $this->requestHandler = $requestHandler;
        $this->timeTracker = $timeTracker;
        $this->typoScriptFrontendInitialization = $typoScriptFrontendInitialization;
    }

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
        $frontendUser = $this->getRequest()->getAttribute('frontend.user');
        return ($frontendUser instanceof FrontendUserAuthentication && is_array($frontendUser->user) && isset($frontendUser->user['uid']));
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
        return $this->getRequest()->getAttribute('frontend.user');
    }

    /**
     * @param integer $pageId
     * @param integer $type
     *
     * @return void
     */
    public function initializeFrontendRendering($pageId = 0, $type = 0)
    {
        if ($this->isFrontendInitialized()) {
            // FE is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            return;
        }

        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($pageId);
        $pageArguments = new PageArguments($pageId, $type, [], [], []);

        /* @var ServerRequestInterface $request */
        $request = $this->getRequest()
            ->withAttribute('site', $site)
            ->withAttribute('routing', $pageArguments)
            ->withAttribute('language', $site->getDefaultLanguage())
            ->withQueryParams($_GET)->withCookieParams($_COOKIE);

        $this->backendUserAuthenticator->process($request, $this->mockedRequestHandler);
        $request = $this->mockedRequestHandler->getRequest();

        $this->frontendUserAuthenticator->process($request, $this->mockedRequestHandler);
        $request = $this->mockedRequestHandler->getRequest();

        $this->typoScriptFrontendInitialization->process($request, $this->mockedRequestHandler);
        $request = $this->mockedRequestHandler->getRequest();

        $prepareTypoScriptFrontendRendering = new PrepareTypoScriptFrontendRendering($GLOBALS['TSFE'], $this->timeTracker);
        $prepareTypoScriptFrontendRendering->process($request, $this->mockedRequestHandler);
        Loader::setRequest($this->mockedRequestHandler->getRequest());
    }

    /**
     * @return string
     * @throws LogicException
     */
    public function renderPageContent()
    {
        if ($this->isFrontendInitialized() === false) {
            throw new LogicException('FrontendRendering is not initialized - initialize with method \'initializeFrontendRendering\'');
        }

        /** @var Response $response */
        $response = $this->requestHandler->handle($this->getRequest());
        return $response->getBody()->__toString();
    }

    /**
     * @param ServerRequestInterface $request
     */
    public static function setRequest(ServerRequestInterface $request)
    {
        $GLOBALS['RESTLER_TYPO3_REQUEST'] = $request;
    }

    /**
     * @return ServerRequestInterface
     */
    private function getRequest()
    {
        return $GLOBALS['RESTLER_TYPO3_REQUEST'];
    }

    /**
     * @return boolean
     */
    private function isFrontendInitialized()
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController &&
            $GLOBALS['TSFE']->tmpl instanceof TemplateService;
    }
}
