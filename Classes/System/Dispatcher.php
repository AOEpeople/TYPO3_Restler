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
use Aoe\Restler\System\TYPO3\Loader;
use Luracast\Restler\Routes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
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

    public function __construct(ObjectManager $objectManager=null)
    {
        if (!$objectManager){
            $objectManager = new ObjectManager();
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
        $restlerObj = $this->restlerBuilder->build();
        if ($this->isRestlerUrl($request->getUri()->getPath())){
            $output = $restlerObj->handle();
            return new Response($output);
        }
        return $handler->handle($request);
    }

    private function isRestlerUrl($uri) {
        foreach (Routes::findAll() as $routes) {
            foreach($routes as $route) {
                if (strpos($uri, rtrim($route["route"]["url"], '/*'))) {
                    return true;
                }
            }
        }
        return false;
    }

}
