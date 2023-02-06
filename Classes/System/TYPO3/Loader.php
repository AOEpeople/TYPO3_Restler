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
use TYPO3\CMS\Core\Context\TypoScriptAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
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
    protected TimeTracker $timeTracker;

    private BackendUserAuthenticator $backendUserAuthenticator;

    private FrontendUserAuthenticator $frontendUserAuthenticator;

    private MockRequestHandler $mockRequestHandler;

    private RequestHandler $requestHandler;

    private TypoScriptFrontendInitialization $typoScriptFrontendInitialization;

    public function __construct(
        BackendUserAuthenticator $backendUserAuthenticator,
        FrontendUserAuthenticator $frontendUserAuthenticator,
        MockRequestHandler $mockRequestHandler,
        RequestHandler $requestHandler,
        TimeTracker $timeTracker,
        TypoScriptFrontendInitialization $typoScriptFrontendInitialization
    ) {
        $this->backendUserAuthenticator = $backendUserAuthenticator;
        $this->frontendUserAuthenticator = $frontendUserAuthenticator;
        $this->mockRequestHandler = $mockRequestHandler;
        $this->requestHandler = $requestHandler;
        $this->timeTracker = $timeTracker;
        $this->typoScriptFrontendInitialization = $typoScriptFrontendInitialization;
    }

    /**
     * Initialize backend-user with BackendUserAuthenticator middleware.
     * @see \TYPO3\CMS\Frontend\Middleware\BackendUserAuthenticator
     */
    public function initializeBackendUser()
    {
        if ($this->hasActiveBackendUser()) {
            // Backend-User is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            return;
        }

        $this->backendUserAuthenticator->process($this->getRequest(), $this->mockRequestHandler);
        self::setRequest($this->mockRequestHandler->getRequest());
    }

    /**
     * Checks if a backend user is logged in.
     */
    public function hasActiveBackendUser(): bool
    {
        return ($GLOBALS['BE_USER'] ?? null) instanceof BackendUserAuthentication &&
            $GLOBALS['BE_USER']->user['uid'] > 0;
    }

    /**
     * @throws LogicException
     */
    public function getBackendUser(): BackendUserAuthentication
    {
        if (!$this->hasActiveBackendUser()) {
            throw new LogicException("be-user is not initialized - initialize with BE-user with method 'initializeBackendUser'");
        }
        return $GLOBALS['BE_USER'];
    }

    /**
     * Initialize frontend-user with FrontendUserAuthenticator middleware.
     * @param string|int $pid List of page IDs (comma separated) or page ID where to look for frontend user records
     * @see \TYPO3\CMS\Frontend\Middleware\FrontendUserAuthenticator
     */
    public function initializeFrontendUser($pid = 0)
    {
        if ($this->hasActiveFrontendUser()) {
            // Frontend-User is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            return;
        }

        /** @var ServerRequestInterface $request */
        $request = $this->getRequest()
            ->withQueryParams(array_merge($_GET, ['pid' => $pid]))
            ->withCookieParams($_COOKIE);

        $this->frontendUserAuthenticator->process($request, $this->mockRequestHandler);
        self::setRequest($this->mockRequestHandler->getRequest());
    }

    /**
     * Checks if a frontend user is logged in and the session is active.
     */
    public function hasActiveFrontendUser(): bool
    {
        $frontendUser = $this->getRequest()
            ->getAttribute('frontend.user');
        return $frontendUser instanceof FrontendUserAuthentication && is_array($frontendUser->user) && isset($frontendUser->user['uid']);
    }

    /**
     * @throws LogicException
     */
    public function getFrontendUser(): FrontendUserAuthentication
    {
        if (!$this->hasActiveFrontendUser()) {
            throw new LogicException('fe-user is not initialized');
        }
        return $this->getRequest()
            ->getAttribute('frontend.user');
    }

    public function initializeFrontendRendering(int $pageId = 0, int $type = 0, bool $forcedTemplateParsing = true)
    {
        if ($this->isFrontendInitialized()) {
            // FE is already initialized - this can happen when we use/call internal REST-endpoints inside of a normal TYPO3-page
            return;
        }

        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        /** @var Site $site */
        $site = $siteFinder->getSiteByPageId($pageId);
        $pageArguments = new PageArguments($pageId, (string) $type, [], [], []);
        $normalizedParams = NormalizedParams::createFromRequest($this->getRequest());

        /** @var ServerRequestInterface $request */
        $request = $this->getRequest()
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('routing', $pageArguments)
            ->withAttribute('language', $site->getDefaultLanguage())
            ->withAttribute('normalizedParams', $normalizedParams)
            ->withQueryParams($_GET)
            ->withCookieParams($_COOKIE);
        self::setRequest($request);

        $this->initializeBackendUser();
        $this->initializeFrontendUser();

        $this->typoScriptFrontendInitialization->process($this->getRequest(), $this->mockRequestHandler);
        self::setRequest($this->mockRequestHandler->getRequest());

        if ($forcedTemplateParsing === true) {
            // Force TemplateParsing (will slow down the called REST-endpoint a little bit):
            // Otherwise we can't render TYPO3-content in REST-endpoints, when TYPO3-cache 'pages' already exists
            /* @var $controller TypoScriptFrontendController */
            $controller = $this->getRequest()->getAttribute('frontend.controller');
            $controller->getContext()->setAspect('typoscript', new TypoScriptAspect($forcedTemplateParsing));
        }

        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version()) > 11005000) {
            // it's TYPO3v11 or higher
            $prepareTypoScriptFrontendRendering = new PrepareTypoScriptFrontendRendering($this->timeTracker);
        } else {
            // it's TYPO3v10 or lower
            $prepareTypoScriptFrontendRendering = new PrepareTypoScriptFrontendRendering($GLOBALS['TSFE']);
        }
        $prepareTypoScriptFrontendRendering->process($this->getRequest(), $this->mockRequestHandler);
        self::setRequest($this->mockRequestHandler->getRequest());
    }

    /**
     * @throws LogicException
     */
    public function renderPageContent(): string
    {
        if (!$this->isFrontendInitialized()) {
            throw new LogicException("FrontendRendering is not initialized - initialize with method 'initializeFrontendRendering'");
        }

        /** @var Response $response */
        $response = $this->requestHandler->handle($this->getRequest());
        return $response->getBody()
            ->__toString();
    }

    public static function setRequest(ServerRequestInterface $request)
    {
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    private function isFrontendInitialized(): bool
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController &&
            $GLOBALS['TSFE']->tmpl instanceof TemplateService;
    }
}
