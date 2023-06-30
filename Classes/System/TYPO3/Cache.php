<?php

namespace Aoe\Restler\System\TYPO3;

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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @package Restler
 */
class Cache implements SingletonInterface
{
    /**
     * This is the phpdoc-tag, which defines, that the response of API-method is cacheable via TYPO3-caching-framework
     *
     * Syntax:
     * The PHPdoc-comment must look like this:
     * @restler_typo3cache_expires [expires-in-seconds]
     *
     * Examples:
     * When API-method should be cacheable in TYPO3 for 10 minutes, than the PHPdoc-comment must look like this:
     * @restler_typo3cache_expires 600
     * When API-method should be cacheable in TYPO3 for endless time, than the PHPdoc-comment must look like this:
     * @restler_typo3cache_expires 0
     *
     * @var string
     */
    public const API_METHOD_TYPO3CACHE_EXPIRES = 'restler_typo3cache_expires';

    /**
     * This is the phpdoc-tag, which defines, that the cached response of API-method should be tagged with given tags
     *
     * Syntax:
     * The PHPdoc-comment must look like this:
     * @restler_typo3cache_tags [comma-separated-list-of-tags]
     *
     * Example:
     * When response of API-method should be tagged with 'tag_a' and 'tag_b', than the PHPdoc-comment must look like this:
     * @restler_typo3cache_tags tag_a,tag_b
     *
     * @var string
     */
    public const API_METHOD_TYPO3CACHE_TAGS = 'restler_typo3cache_tags';

    private FrontendInterface $cache;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getCache('restler');
    }

    public function isResponseCacheableByTypo3Cache(string $requestMethod, array $apiMethodInfoMetadata): bool
    {
        if ($requestMethod === 'GET' &&
            array_key_exists(self::API_METHOD_TYPO3CACHE_EXPIRES, $apiMethodInfoMetadata) &&
            array_key_exists(self::API_METHOD_TYPO3CACHE_TAGS, $apiMethodInfoMetadata)) {
            return true;
        }
        return false;
    }

    /**
     * cache response
     */
    public function cacheResponseByTypo3Cache(
        int $responseCode,
        string $requestUri,
        array $requestGetData,
        array $apiMethodInfoMetadata,
        string $responseData,
        string $responseFormatClass,
        array $responseHeaders
    ) {
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);
        $frontendCacheExpires = (int) ($apiMethodInfoMetadata['expires'] ?? 0);
        $typo3CacheExpires = (int) $apiMethodInfoMetadata[self::API_METHOD_TYPO3CACHE_EXPIRES];
        $typo3CacheTags = explode(',', $apiMethodInfoMetadata[self::API_METHOD_TYPO3CACHE_TAGS]);

        $cacheData = [];
        $cacheData['responseCode'] = $responseCode;
        $cacheData['requestUri'] = $requestUri;
        $cacheData['requestGetData'] = $requestGetData;
        $cacheData['responseData'] = $responseData;
        $cacheData['responseFormatClass'] = $responseFormatClass;
        $cacheData['responseHeaders'] = $responseHeaders;
        $cacheData['frontendCacheExpires'] = $frontendCacheExpires;

        $this->cache->set($identifier, $cacheData, $typo3CacheTags, $typo3CacheExpires);
    }

    public function getCacheEntry(string $requestUri, array $getData)
    {
        $identifier = $this->buildIdentifier($requestUri, $getData);
        return $this->cache->get($identifier);
    }

    public function hasCacheEntry(string $requestUri, array $getData): bool
    {
        $identifier = $this->buildIdentifier($requestUri, $getData);
        return $this->cache->has($identifier);
    }

    public function flushByTag(string $tag)
    {
        $this->cache->flushByTag($tag);
    }

    private function buildIdentifier(string $requestUri, array $getData): string
    {
        return md5($requestUri . serialize($getData));
    }
}
