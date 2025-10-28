<?php

declare(strict_types=1);

namespace Aoe\Restler\Tests\Unit\System\TYPO3;

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

use Aoe\Restler\System\TYPO3\Cache;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * @package Restler
 * @subpackage Tests
 */
final class CacheTest extends BaseTestCase
{
    private Cache $cache;

    private FrontendInterface&MockObject $frontendCacheMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->frontendCacheMock = $this->createMock(FrontendInterface::class);
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock->expects($this->once())
            ->method('getCache')
            ->with('restler')
            ->willReturn($this->frontendCacheMock);

        $this->cache = new Cache($cacheManagerMock);
    }

    public function testResponseShouldBeCacheableWhenRestEndpointUseGetMethodAndTypo3CacheIsConfigured(): void
    {
        $requestMethod = 'GET';
        $apiMethodInfoMetadata = [];
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 0;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a';

        $this->assertTrue($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    public function testResponseShouldNotBeCacheableWhenRestEndpointUseNoGetMethod(): void
    {
        $requestMethod = 'POST';
        $apiMethodInfoMetadata = [];
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 0;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a';

        $this->assertFalse($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    public function testResponseShouldNotBeCacheableWhenTypo3CacheIsNotConfigured(): void
    {
        $requestMethod = 'GET';
        $apiMethodInfoMetadata = [];

        $this->assertFalse($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    public function testResponseShouldBeCached(): void
    {
        $responseCode = 123;
        $requestUri = 'api/shop/devices';
        $requestGetData = ['limit' => 10];
        $apiMethodInfoMetadata = [];
        $apiMethodInfoMetadata['expires'] = 600;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 3600;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a,tag_b';
        $responseData = 'this-would-be-the-json-response-which-we-want-cache';
        $responseFormatClass = 'this-would-be-the-php-class-which-can-decode/encode-the-json-response';
        $responseHeaders = ['header1', 'header2'];

        $identifier = $this->buildIdentifier($requestUri, $requestGetData);
        $cacheData = [];
        $cacheData['responseCode'] = $responseCode;
        $cacheData['requestUri'] = $requestUri;
        $cacheData['requestGetData'] = $requestGetData;
        $cacheData['responseData'] = $responseData;
        $cacheData['responseFormatClass'] = $responseFormatClass;
        $cacheData['responseHeaders'] = $responseHeaders;
        $cacheData['frontendCacheExpires'] = $apiMethodInfoMetadata['expires'];
        $typo3CacheTags = ['tag_a', 'tag_b'];
        $typo3CacheExpires = $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES];

        $this->frontendCacheMock->expects($this->once())
            ->method('set')
            ->with($identifier, $cacheData, $typo3CacheTags, $typo3CacheExpires);

        $this->cache->cacheResponseByTypo3Cache(
            $responseCode,
            $requestUri,
            $requestGetData,
            $apiMethodInfoMetadata,
            $responseData,
            $responseFormatClass,
            $responseHeaders
        );
    }

    public function testShouldGetCacheEntry(): void
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = ['limit' => 10];
        $response = 'this-would-be-the-json-response-from-cache';
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())
            ->method('get')
            ->with($identifier)
            ->willReturn($response);
        $this->assertSame($response, $this->cache->getCacheEntry($requestUri, $requestGetData));
    }

    public function testShouldHaveCacheEntry(): void
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = ['limit' => 10];
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())
            ->method('has')
            ->with($identifier)
            ->willReturn(true);
        $this->assertTrue($this->cache->hasCacheEntry($requestUri, $requestGetData));
    }

    public function testShouldHaveNoCacheEntry(): void
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = ['limit' => 10];
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())
            ->method('has')
            ->with($identifier)
            ->willReturn(false);
        $this->assertFalse($this->cache->hasCacheEntry($requestUri, $requestGetData));
    }

    public function testShouldFlushCacheByTag(): void
    {
        $tag = 'tag_a';

        $this->frontendCacheMock->expects($this->once())
            ->method('flushByTag')
            ->with($tag);
        $this->cache->flushByTag($tag);
    }

    private function buildIdentifier(string $requestUri, array $getData): string
    {
        return md5($requestUri . serialize($getData));
    }
}
