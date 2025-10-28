<?php

declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @package Restler
 * @subpackage Tests
 */
final class RestApiClientTest extends BaseTestCase
{
    private ExtensionConfiguration&MockObject $extensionConfigurationMock;

    private RestApiClient&MockObject $restApiClient;

    private RestApiRequest&MockObject $restApiRequestMock;

    private RestApiRequestScope&MockObject $restApiRequestScopeMock;

    private RestlerBuilder&MockObject $restlerBuilderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $this->restApiRequestMock = $this->createMock(RestApiRequest::class);
        $this->restApiRequestScopeMock = $this->createMock(RestApiRequestScope::class);
        $this->restlerBuilderMock = $this->createMock(RestlerBuilder::class);
        $typo3CacheMock = $this->createMock(Typo3Cache::class);

        $this->restApiClient = $this->getMockBuilder(RestApiClient::class)
            ->setConstructorArgs([$this->extensionConfigurationMock, $this->restApiRequestScopeMock, $typo3CacheMock])
            ->onlyMethods(['createRequest', 'getRestlerBuilder', 'isRequestPreparationRequired'])
            ->getMock();
        $this->restApiClient->method('createRequest')
            ->willReturn($this->restApiRequestMock);
        $this->restApiClient->method('getRestlerBuilder')
            ->willReturn($this->restlerBuilderMock);
    }

    public function testCanCheckIfProductionContextIsSet(): void
    {
        $this->extensionConfigurationMock->expects($this->once())
            ->method('isProductionContextSet')
            ->willReturn(true);
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
        $this->restApiClient->expects($this->once())
            ->method('isRequestPreparationRequired')
            ->willReturn(false);
        $this->restlerBuilderMock->expects($this->never())
            ->method('build');
        $this->restApiRequestScopeMock->expects($this->never())
            ->method('storeOriginalRestApiRequest');

        // Test, that we get a result when we execute the REST-API-request
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
        $originalRestApiRequestMock = $this->createMock(Restler::class);
        $this->restApiClient->expects($this->once())
            ->method('isRequestPreparationRequired')
            ->willReturn(true);
        $this->restlerBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($originalRestApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('storeOriginalRestApiRequest')
            ->with($originalRestApiRequestMock);

        // Test, that we get a result when we execute the REST-API-request
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
