<?php
namespace Aoe\Restler\Tests\Unit\System\TYPO3;

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

use Aoe\Restler\System\TYPO3\Cache;
use Aoe\Restler\Tests\Unit\BaseTest;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\TYPO3\Cache
 */
class CacheTest extends BaseTest
{
    /**
     * @var Cache
     */
    protected $cache;
    /**
     * @var FrontendInterface
     */
    protected $frontendCacheMock;


    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->frontendCacheMock = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\Frontend\\FrontendInterface')
            ->disableOriginalConstructor()->getMock();
        $cacheManagerMock = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\CacheManager')->disableOriginalConstructor()->getMock();
        $cacheManagerMock->expects($this->once())->method('getCache')->with('cache_restler')->willReturn($this->frontendCacheMock);

        $this->cache = new Cache($cacheManagerMock);
    }

    /**
     * @test
     */
    public function responseShouldBeCacheableWhenRestEndpointUseGetMethodAndTypo3CacheIsConfigured()
    {
        $requestMethod = 'GET';
        $apiMethodInfoMetadata = array();
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 0;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a';

        $this->assertTrue($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    /**
     * @test
     */
    public function responseShouldNotBeCacheableWhenRestEndpointUseNoGetMethod()
    {
        $requestMethod = 'POST';
        $apiMethodInfoMetadata = array();
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 0;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a';

        $this->assertFalse($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    /**
     * @test
     */
    public function responseShouldNotBeCacheableWhenTypo3CacheIsNotConfigured()
    {
        $requestMethod = 'GET';
        $apiMethodInfoMetadata = array();

        $this->assertFalse($this->cache->isResponseCacheableByTypo3Cache($requestMethod, $apiMethodInfoMetadata));
    }

    /**
     * @test
     */
    public function responseShouldBeCached()
    {
        $responseCode = 123;
        $requestUri = 'api/shop/devices';
        $requestGetData = array('limit' => 10);
        $apiMethodInfoMetadata = array();
        $apiMethodInfoMetadata['expires'] = 600;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES] = 3600;
        $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_TAGS] = 'tag_a,tag_b';
        $responseData = 'this-would-be-the-json-response-which-we-want-cache';
        $responseFormatClass = 'this-would-be-the-php-class-which-can-decode/encode-the-json-response';
        $responseHeaders = array('header1', 'header2');

        $identifier = $this->buildIdentifier($requestUri, $requestGetData);
        $cacheData = array();
        $cacheData['responseCode'] = $responseCode;
        $cacheData['requestUri'] = $requestUri;
        $cacheData['requestGetData'] = $requestGetData;
        $cacheData['responseData'] = $responseData;
        $cacheData['responseFormatClass'] = $responseFormatClass;
        $cacheData['responseHeaders'] = $responseHeaders;
        $cacheData['frontendCacheExpires'] = $apiMethodInfoMetadata['expires'];
        $typo3CacheTags = array('tag_a', 'tag_b');
        $typo3CacheExpires = $apiMethodInfoMetadata[Cache::API_METHOD_TYPO3CACHE_EXPIRES];

        $this->frontendCacheMock->expects($this->once())->method('set')->with($identifier, $cacheData, $typo3CacheTags, $typo3CacheExpires);

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

    /**
     * @test
     */
    public function shouldGetCacheEntry()
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = array('limit' => 10);
        $response = 'this-would-be-the-json-response-from-cache';
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())->method('get')->with($identifier)->willReturn($response);
        $this->assertEquals($response, $this->cache->getCacheEntry($requestUri, $requestGetData));
    }

    /**
     * @test
     */
    public function shouldHaveCacheEntry()
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = array('limit' => 10);
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())->method('has')->with($identifier)->willReturn(true);
        $this->assertTrue($this->cache->hasCacheEntry($requestUri, $requestGetData));
    }

    /**
     * @test
     */
    public function shouldHaveNoCacheEntry()
    {
        $requestUri = 'api/shop/devices';
        $requestGetData = array('limit' => 10);
        $identifier = $this->buildIdentifier($requestUri, $requestGetData);

        $this->frontendCacheMock->expects($this->once())->method('has')->with($identifier)->willReturn(false);
        $this->assertFalse($this->cache->hasCacheEntry($requestUri, $requestGetData));
    }

    /**
     * @test
     */
    public function shouldFlushCacheByTag()
    {
        $tag = 'tag_a';

        $this->frontendCacheMock->expects($this->once())->method('flushByTag')->with($tag);
        $this->cache->flushByTag($tag);
    }

    /**
     * @param string $requestUri
     * @param array $getData
     * @return string
     */
    private function buildIdentifier($requestUri, array $getData)
    {
        return md5($requestUri . serialize($getData));
    }
}
