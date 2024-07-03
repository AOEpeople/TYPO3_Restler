<?php

namespace Aoe\Restler\Tests\Unit\System\RestApi;

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

use Aoe\Restler\System\RestApi\RestApiRequest;
use Aoe\Restler\System\RestApi\RestApiRequestScope;
use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Exception;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\RestApi\RestApiRequest
 */
class RestApiRequestTest extends BaseTestCase
{
    /**
     * @var array
     */
    protected $originalGetVars;

    /**
     * @var array
     */
    protected $originalPostVars;

    /**
     * @var array
     */
    protected $originalServerSettings;

    /**
     * @var RestApiRequest
     */
    protected $restApiRequest;

    /**
     * @var RestApiRequestScope
     */
    protected $restApiRequestScopeMock;

    /**
     * @var Typo3Cache
     */
    protected $typo3CacheMock;

    /**
     * setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        // store the global data ($_GET, $_POST, $_SERVER)
        $this->originalGetVars = $_GET;
        $this->originalPostVars = $_POST;
        $this->originalServerSettings = $_SERVER;

        $this->restApiRequestScopeMock = $this->getMockBuilder(RestApiRequestScope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typo3CacheMock = $this->getMockBuilder(Typo3Cache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->restApiRequest = $this->getMockBuilder(RestApiRequest::class)
            ->setConstructorArgs([$this->restApiRequestScopeMock, $this->typo3CacheMock])
            ->onlyMethods(['handle'])
            ->getMock();
    }

    /**
     * tear down
     */
    protected function tearDown(): void
    {
        // restore the global data ($_GET, $_POST, $_SERVER)
        $_GET = $this->originalGetVars;
        $_POST = $this->originalPostVars;
        $_SERVER = $this->originalServerSettings;

        parent::tearDown();
    }

    public function testCanExecuteRestApiRequest(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $result = ['id' => 1, 'name' => 'Test-Product'];

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $this->restApiRequestScopeMock->expects(self::once())->method('storeOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects(self::once())->method('storeOriginalRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects(self::once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects(self::once())->method('handle')->willReturn($result);
        $this->assertSame($result, $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     */
    public function testShouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestIsDone(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $result = ['id' => 1, 'name' => 'Test-Product'];

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects(self::once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects(self::once())->method('handle')->willReturn($result);
        $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     */
    public function testShouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestFailsWithException(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $exception = new Exception();

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects(self::once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects(self::once())->method('handle')->willThrowException($exception);

        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);
        } catch (Exception) {
        }

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     */
    public function testShouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestFailsWithRestException(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $exception = new RestException(400, 'message');

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects(self::once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects(self::once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects(self::once())->method('handle')->willThrowException($exception);

        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);
        } catch (Exception) {
        }

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    public function testShouldThrowRestExceptionWhenExecutionOfRestApiRequestFailsWithException(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $exception = new Exception();

        $this->restApiRequest->expects(self::once())->method('handle')->willThrowException($exception);

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);

        self::expectException(RestException::class);
        $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
    }

    public function testShouldThrowRestExceptionWhenExecutionOfRestApiRequestFailsWithRestException(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $exception = new RestException(400, 'message');

        $this->restApiRequest->expects(self::once())->method('handle')->willThrowException($exception);

        $originalRestApiRequest = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $this->restApiRequestScopeMock->expects(self::once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);

        self::expectException(RestException::class);
        $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
    }
}
