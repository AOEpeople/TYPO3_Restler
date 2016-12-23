<?php
namespace Aoe\Restler\System\Restler;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 AOE GmbH <dev@aoe.com>
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
use Luracast\Restler\Restler;
use Luracast\Restler\RestException;
use Exception;

/**
 * @package Restler
 */
class RestlerExtended extends Restler
{
    /**
     * @var Typo3Cache
     */
    private $typo3Cache;



    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /* Block of methods, which MUST be overriden from parent-class (otherwise we can't use the TYPO3-caching-framework) ********/
    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /**
     * Constructor
     *
     * @param Typo3Cache $typo3Cache
     * @param boolean $productionMode    When set to false, it will run in
     *                                   debug mode and parse the class files
     *                                   every time to map it to the URL
     *
     * @param boolean $refreshCache      will update the cache when set to true
     */
    public function __construct(Typo3Cache $typo3Cache, $productionMode = false, $refreshCache = false)
    {
        // adds format support for application/hal+json during format negotiation
        $this->formatMap["application/hal+json"] = "JsonFormat";

        parent::__construct($productionMode, $refreshCache);
        $this->typo3Cache = $typo3Cache;
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
     * override postCall so that we can cache response via TYPO3-caching-framework - if it's possible
     */
    protected function postCall()
    {
        parent::postCall();

        if ($this->typo3Cache->isResponseCacheableByTypo3Cache($this->requestMethod, $this->apiMethodInfo->metadata)) {
            $this->typo3Cache->cacheResponseByTypo3Cache(
                $this->url,
                $_GET,
                $this->apiMethodInfo->metadata,
                $this->responseData,
                get_class($this->responseFormat),
                headers_list()
            );
        }
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
                    @header('Expires: '.$expires);
                } else {
                    @header($responseHeader);
                }
            }
        }
        @header('X-Cached-By-Typo3: 1');

        // send data to client
        $this->responseData = $cacheEntry['responseData'];
        return $this->respond();
    }
}
