<?php
namespace Aoe\Restler\Http;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 AOE GmbH <dev@aoe.com>
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

use Aoe\Restler\System\Dispatcher;
use Aoe\Restler\System\DispatcherWithoutMiddlewareImplementation;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Http\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * This is the main entry point for everything starting with /api/
 */
class RestRequestHandler implements RequestHandlerInterface
{
    /**
     * Instance of the current TYPO3 bootstrap
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * The request handed over
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Constructor handing over the bootstrap
     *
     * @param Bootstrap $bootstrap
     */
    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Handles a frontend request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return NULL|\Psr\Http\Message\ResponseInterface
     */
    public function handleRequest(\Psr\Http\Message\ServerRequestInterface $request)
    {
        // We define this constant, so that any TYPO3-Extension can check, if the REST-API is running
        define('REST_API_IS_RUNNING', true);

        // Dispatch the API-call
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if (interface_exists('\Psr\Http\Server\MiddlewareInterface')) {
            $objectManager->get(Dispatcher::class)->dispatch();
        } else {
            $objectManager->get(DispatcherWithoutMiddlewareImplementation::class)->dispatch();
        }
    }

    /**
     * This request handler can handle any frontend request.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool If the request is not an eID request, TRUE otherwise FALSE
     */
    public function canHandleRequest(ServerRequestInterface $request)
    {
        return $this->isRequestApi($request) || $this->isRequestApiExplorer($request);
    }

    /**
     * Returns the priority.
     *
     * Shows how eager the handler is to actually handle the
     * request. An integer > 0 means "I want to handle this request" where
     * "100" is default. "0" means "I am a fallback solution"
     *
     * @return int The priority of the request handler.
     */
    public function getPriority()
    {
        return 60;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function isRequestApi(ServerRequestInterface $request)
    {
        return strpos($request->getUri()->getPath(), '/api/') === 0;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function isRequestApiExplorer(ServerRequestInterface $request)
    {
        return strpos($request->getUri()->getPath(), '/api_explorer/') === 0;
    }
}