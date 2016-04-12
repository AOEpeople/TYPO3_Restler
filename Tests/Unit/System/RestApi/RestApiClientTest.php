<?php
namespace Aoe\Restler\Tests\Unit\System\RestApi;

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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\System\RestApi\RestApiClient;
use Aoe\Restler\System\RestApi\RestApiRequest;
use Aoe\Restler\System\RestApi\RestApiRequestScope;
use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\RestException;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\RestApi\RestApiClient
 */
class RestApiClientTest extends BaseTest
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

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->getMockBuilder('Aoe\\Restler\\Configuration\\ExtensionConfiguration')
            ->disableOriginalConstructor()->getMock();
        $this->restApiRequestMock = $this->getMockBuilder('Aoe\\Restler\\System\\RestApi\\RestApiRequest')
            ->disableOriginalConstructor()->getMock();
        $this->restApiRequestScopeMock = $this->getMockBuilder('Aoe\\Restler\\System\\RestApi\\RestApiRequestScope')
            ->disableOriginalConstructor()->getMock();
        $this->restlerBuilderMock = $this->getMockBuilder('Aoe\\Restler\\System\\Restler\\Builder')
            ->disableOriginalConstructor()->getMock();
        $this->typo3CacheMock = $this->getMockBuilder('Aoe\\Restler\\System\\TYPO3\\Cache')
            ->disableOriginalConstructor()->getMock();

        $this->restApiClient = $this->getMockBuilder('Aoe\\Restler\\System\\RestApi\\RestApiClient')
            ->setConstructorArgs(array($this->extensionConfigurationMock, $this->restApiRequestScopeMock, $this->typo3CacheMock))
            ->setMethods(array('createRequest', 'getRestlerBuilder', 'isRequestPreparationRequired'))
            ->getMock();
        $this->restApiClient->expects($this->any())->method('createRequest')->willReturn($this->restApiRequestMock);
        $this->restApiClient->expects($this->any())->method('getRestlerBuilder')->willReturn($this->restlerBuilderMock);
    }

    /**
     * @test
     */
    public function canCheckIfProductionContextIsSet()
    {
        $this->extensionConfigurationMock->expects($this->once())->method('isProductionContextSet')->willReturn(true);
        $this->assertTrue($this->restApiClient->isProductionContextSet());
    }

    /**
     * @test
     */
    public function canCheckIfRequestIsBeingExecuted()
    {
        $this->assertFalse($this->restApiClient->isExecutingRequest());
    }

    /**
     * Test, that we don't must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
     *
     * @test
     */
    public function canExecuteRequestInRestApiContext()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $result = array('id' => 1, 'name' => 'Test-Product');

        // Test, that we don't must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
        $this->restApiClient->expects($this->once())->method('isRequestPreparationRequired')->willReturn(false);
        $this->restlerBuilderMock->expects($this->never())->method('build');
        $this->restApiRequestScopeMock->expects($this->never())->method('storeOriginalRestApiRequest');

        // Test, that we get an result when we execute the REST-API-request
        $this->restApiRequestMock->expects($this->once())->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->willReturn($result);
        $this->assertEquals($result, $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * Test, that we must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
     *
     * @test
     */
    public function canExecuteRequestInTypo3Context()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $result = array('id' => 1, 'name' => 'Test-Product');

        // Test, that we must create the 'original' REST-API-Request (aka Restler-object) before we can execute the REST-API-request
        $originalRestApiRequestMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $this->restApiClient->expects($this->once())->method('isRequestPreparationRequired')->willReturn(true);
        $this->restlerBuilderMock->expects($this->once())->method('build')->willReturn($originalRestApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())->method('storeOriginalRestApiRequest')->with($originalRestApiRequestMock);

        // Test, that we get an result when we execute the REST-API-request
        $this->restApiRequestMock->expects($this->once())->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->willReturn($result);
        $this->assertEquals($result, $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * @test
     * @expectedException Aoe\Restler\System\RestApi\RestApiRequestException
     * @expectedExceptionCode 1446475601
     */
    public function shouldThrowExceptionWhenExecutionOfRequestFails()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $exception = new RestException(400, 'message');

        $this->restApiClient->expects($this->once())->method('isRequestPreparationRequired')->willReturn(false);
        $this->restApiRequestMock->expects($this->once())->method('executeRestApiRequest')
            ->with($requestMethod, $requestUri, $getData, $postData)
            ->will($this->throwException($exception));

        $this->restApiClient->executeRequest($requestMethod, $requestUri, $getData, $postData);
    }
}
