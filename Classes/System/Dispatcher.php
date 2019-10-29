<?php
namespace Aoe\Restler\System;

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

use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @package Restler
 */
class Dispatcher implements MiddlewareInterface
{
    /**
     * @var RestlerBuilder
     */
    private $restlerBuilder;

    public function __construct(ObjectManager $objectManager = null)
    {
        if (!$objectManager) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }
        $this->restlerBuilder = $objectManager->get(RestlerBuilder::class);
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $restlerObj = $this->restlerBuilder->build($request);

        if ($this->isRestlerUrl('/' . $restlerObj->url)) {
            /**
             * We might end up with a loaded TSFE->config but an empty
             * TSFE->tmpl->setup. That is depending on the state of the caches.
             * This in turn will lead to an empty extbase configuration.
             * And this will lead to failures loading sys_file_reference
             * as it will use the default tableName of tx_extbase_domain_model_filereference
             */
            // check for proper template config state
            if (!$GLOBALS['TSFE']->tmpl->loaded) {
                if (empty($GLOBALS['TSFE']->rootLine) && !empty($GLOBALS['TSFE']->id)) {
                    $GLOBALS['TSFE']->determineId();
                    if ($GLOBALS['TSFE']->tmpl === null) {
                        $GLOBALS['TSFE']->getConfigArray();
                    }
                }

                if (!empty($GLOBALS['TSFE']->tmpl) && !empty($GLOBALS['TSFE']->rootLine)) {
                    $GLOBALS['TSFE']->tmpl->start($GLOBALS['TSFE']->rootLine);
                }
            }


            // wrap reponse into a stream to pass along to the rest of the Typo3 framework
            $body = new Stream('php://temp', 'wb+');
            $body->write($restlerObj->handle());
            $body->rewind();

            return new Response($body, $restlerObj->responseCode);
        }
        return $handler->handle($request);
    }

    private function isRestlerUrl($uri): bool
    {
        return \Aoe\Restler\System\Restler\Routes::containsUrl($uri);
    }

}
