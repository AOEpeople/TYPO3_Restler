<?php

namespace Aoe\Restler\Tests\Unit\System\RestApi;

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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\System\RestApi\RestApiClient;
use Aoe\Restler\System\RestApi\RestApiRequest;
use Aoe\Restler\System\RestApi\RestApiRequestException;
use Aoe\Restler\System\RestApi\RestApiRequestScope;
use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 */
class RestApiClientTest extends BaseTestCase
{
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfigurationMock;

    /**
     * @var RestApiClient
     */
    protected $restApiClient;

    /**
     * @var RestApiRequest
     */
    protected $restApiRequestMock;

    /**
     * @var RestApiRequestScope
     */
    protected $restApiRequestScopeMock;

    /**
     * @var RestlerBuilder
     */
    protected $restlerBuilderMock;

    /**
     * @var Typo3Cache
     */
    protected $typo3CacheMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->getMockBuilder(ExtensionConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restApiRequestMock = $this->getMockBuilder(RestApiRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restApiRequestScopeMock = $this->getMockBuilder(RestApiRequestScope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restlerBuilderMock = $this->getMockBuilder(RestlerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typo3CacheMock = $this->getMockBuilder(Typo3Cache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->restApiClient = $this->getMockBuilder(RestApiClient::class)
            ->setConstructorArgs([$this->extensionConfigurationMock, $this->restApiRequestScopeMock, $this->typo3CacheMock])
            ->onlyMethods(['createRequest', 'getRestlerBuilder', 'isRequestPreparationRequired'])
            ->getMock();
        $this->restApiClient->expects(self::any())->method('createRequest')->willReturn($this->restApiRequestMock);
        $this->restApiClient->expects(self::any())->method('getRestlerBuilder')->willReturn($this->restlerBuilderMock);
    }

    public function testCanCheckIfProductionContextIsSet(): void
    {
        $this->extensionConfigurationMock->expects(self::once())->method('isProductionContextSet')->willReturn(true);
        $this->assertTrue($this->restApiClient->isProductionContextSet());
    }

    public function testCanCheckIfRequestIsBeingExecuted(): void
    {
        $this->assertFalse($this->restApiClient->isExecutingRequest());
    }

    /**
     * Test, that we don't must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
     */
    public function testCanExecuteRequestInRestApiContext(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $result = ['id' => 1, 'name' => 'Test-Product'];

        // Test, that we don't must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
        $this->restApiClient->expects(self::once())->method('isRequestPreparationRequired')->willReturn(false);
        $this->restlerBuilderMock->expects(self::never())->method('build');
        $this->restApiRequestScopeMock->expects(self::never())->method('storeOriginalRestApiRequest');

        // Test, that we get an result when we execute the REST-API-request
        $this->restApiRequestMock->expects($this->once())
            ->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->willReturn($result);
        $this->assertSame($result, $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * Test, that we must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
     */
    public function testCanExecuteRequestInTypo3Context(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $result = ['id' => 1, 'name' => 'Test-Product'];

        // Test, that we must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
        $originalRestApiRequestMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $this->restApiClient->expects($this->once())
            ->method('isRequestPreparationRequired')
            ->willReturn(true);
        $this->restlerBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($originalRestApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('storeOriginalRestApiRequest')
            ->with($originalRestApiRequestMock);

        // Test, that we get an result when we execute the REST-API-request
        $this->restApiRequestMock->expects($this->once())
            ->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->willReturn($result);
        $this->assertSame($result, $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData));
    }

    public function testShouldThrowExceptionWhenExecutionOfRequestFails(): void
    {
        $this->expectException(RestApiRequestException::class);
        $this->expectExceptionCode(1446475601);

        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $exception = new RestException(400, 'message');

        $this->restApiClient->expects($this->once())
            ->method('isRequestPreparationRequired')
            ->willReturn(false);
        $this->restApiRequestMock->expects($this->once())
            ->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->willThrowException($exception);

        $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData);
    }
}
