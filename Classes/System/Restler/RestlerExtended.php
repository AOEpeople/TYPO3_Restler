<?php
namespace Aoe\Restler\System\Restler;

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

use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Exception;
use Luracast\Restler\Defaults;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

class RestlerExtended extends Restler
{
    /**
     * @var Typo3Cache
     */
    private $typo3Cache;

    /** @var ServerRequestInterface  */
    protected $request;

    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /* Block of methods, which MUST be overriden from parent-class (otherwise we can't use the TYPO3-caching-framework) ********/
    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /**
     * Constructor
     *
     * @param Typo3Cache $typo3Cache
     * @param bool $productionMode    When set to false, it will run in
     *                                   debug mode and parse the class files
     *                                   every time to map it to the URL
     *
     * @param bool $refreshCache      will update the cache when set to true
     * @param ServerRequestInterface     frontend request
     */
    public function __construct(Typo3Cache $typo3Cache, $productionMode = false, $refreshCache = false, ServerRequestInterface $request = null)
    {
        parent::__construct($productionMode, $refreshCache);

        if (interface_exists('\Psr\Http\Server\MiddlewareInterface')) {
            // restler uses echo;die otherwise and then Typo3 standard mechanisms will not be called
            Defaults::$returnResponse = true;
        }

        // adds format support for application/hal+json
        Scope::$classAliases['HalJsonFormat'] = 'Aoe\Restler\System\Restler\Format\HalJsonFormat';
        $this->setSupportedFormats('HalJsonFormat');

        $this->typo3Cache = $typo3Cache;
        $this->request = $request;

        // set pathes from request if present
        if ($this->request !== null) {
            $this->url = $this->getPath();
        }
    }

    /**
     * Main function for processing the api request
     * and return the response
     *
     * @throws Exception     when the api service class is missing
     * @throws RestException to send error response
     */
    public function handle()
    {
        // get information about the REST-request
        $this->get();

        if ($this->requestMethod === 'GET' && $this->typo3Cache->hasCacheEntry($this->url, $_GET)) {
            return $this->handleRequestByTypo3Cache();
        }

        // if no cache exist: restler should handle the request
        return parent::handle();
    }

    /**
     * Determine path (and baseUrl) for current request.
     *
     * @return string|string[]|null
     */
    protected function getPath()
    {
        if ($this->request !== null) {
            // set base path depending on site config
            $site = $this->request->getAttribute('site');
            if ($site !== null && $site instanceof Site) {
                $siteBasePath = $this->request->getAttribute('site')->getBase()->getPath();
                if ($siteBasePath !== '/' && $siteBasePath[-1] !== '/') {
                    $siteBasePath .= '/';
                }
            } else {
                $siteBasePath = '/';
            }
            $this->baseUrl = (string)$this->request->getUri()->withQuery('')->withPath($siteBasePath);

            // set url with base path removed
            return rtrim(preg_replace('%^' . preg_quote($siteBasePath, '%') . '%', '', $this->request->getUri()->getPath()), '/');
        }
        return parent::getPath();
    }

    /**
     * override postCall so that we can cache response via TYPO3-caching-framework - if it's possible
     */
    protected function postCall()
    {
        parent::postCall();

        if ($this->typo3Cache->isResponseCacheableByTypo3Cache($this->requestMethod, $this->apiMethodInfo->metadata)) {
            $this->typo3Cache->cacheResponseByTypo3Cache(
                $this->responseCode,
                $this->url,
                $_GET,
                $this->apiMethodInfo->metadata,
                $this->responseData,
                get_class($this->responseFormat),
                headers_list()
            );
        }
    }

    /**
     * Rewrap the not accessible private stream in a new one.
     *
     * @return bool|resource
     */
    public function getRequestStream()
    {
        $stream = fopen('php://temp', 'wb+');
        fwrite($stream, (string)$this->request->getBody());
        fseek($stream, 0, SEEK_SET);

        return $stream;
    }

    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /* Block of methods, which does NOT override logic from parent-class *******************************************************/
    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /**
     * @return string
     */
    private function handleRequestByTypo3Cache()
    {
        $cacheEntry = $this->typo3Cache->getCacheEntry($this->url, $_GET);

        if (count($cacheEntry['responseHeaders']) === 0) {
            // the cache is from an internal REST-API-call, so we must manually send some headers
            @header('Content-Type: application/json; charset=utf-8');
            @header('Cache-Control: private, no-cache, no-store, must-revalidate');
        } else {
            // set/manipulate headers
            foreach ($cacheEntry['responseHeaders'] as $responseHeader) {
                if (substr($responseHeader, 0, 8) === 'Expires:') {
                    if ($cacheEntry['frontendCacheExpires'] === 0) {
                        $expires = $cacheEntry['frontendCacheExpires'];
                    } else {
                        $expires = gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheEntry['frontendCacheExpires']);
                    }
                    @header('Expires: ' . $expires);
                } else {
                    @header($responseHeader);
                }
            }
        }
        @header('X-Cached-By-Typo3: 1');

        // send data to client
        $this->responseCode = $cacheEntry['responseCode'];
        $this->responseData = $cacheEntry['responseData'];
        return $this->respond();
    }
}
