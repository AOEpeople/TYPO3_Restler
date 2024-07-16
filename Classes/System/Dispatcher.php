<?php

namespace Aoe\Restler\System;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2024 AOE GmbH <dev@aoe.com>
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

use Aoe\Restler\System\Restler\Routes;
use Aoe\Restler\System\TYPO3\Loader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;

class Dispatcher extends RestlerBuilderAware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        Loader::setRequest($request);

        if ($this->isRestlerPrefix($this->extractSiteUrl($request))) {
            $restlerObj = $this->getRestlerBuilder()
                ->build($request);

            if ($this->isRestlerUrl('/' . $restlerObj->url)) {
                // wrap reponse into a stream to pass along to the rest of the Typo3 framework
                $body = new Stream('php://temp', 'wb+');
                $body->write($restlerObj->handle());
                $body->rewind();

                return new Response($body, $restlerObj->responseCode);
            }
        }

        return $handler->handle($request);
    }

    protected function extractSiteUrl(ServerRequestInterface $request): string
    {
        // set base path depending on site config
        $site = $request->getAttribute('site');
        if ($site instanceof \TYPO3\CMS\Core\Site\Entity\Site) {
            $siteBasePath = $request->getAttribute('site')
                ->getBase()
                ->getPath();
            if ($siteBasePath === '/' || $siteBasePath === '') {
                $siteBasePath = null;
            } elseif ($siteBasePath[-1] !== '/') {
                $siteBasePath .= '/';
            }
        } else {
            $siteBasePath = null;
        }

        if ($siteBasePath) {
            return '/' . rtrim(
                (string) preg_replace(
                    '%^' . preg_quote((string) $siteBasePath, '%') . '%',
                    '',
                    $request->getUri()
                        ->getPath()
                ),
                '/'
            );
        }

        return $request->getUri()
            ->getPath();
    }

    private function isRestlerUrl(string $uri): bool
    {
        return Routes::containsUrl($uri);
    }
}
