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

use Aoe\Restler\System\RestApi\RestApiRequest;
use Aoe\Restler\System\RestApi\RestApiRequestScope;
use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Exception;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @package Restler
 * @subpackage Tests
 */
final class RestApiRequestTest extends BaseTestCase
{
    private array $originalGetVars;

    private array $originalPostVars;

    private array $originalServerSettings;

    private RestApiRequest&MockObject $restApiRequestMock;

    private RestApiRequestScope&MockObject $restApiRequestScopeMock;

    protected function setUp(): void
    {
        parent::setUp();

        // store the global data ($_GET, $_POST, $_SERVER)
        $this->originalGetVars = $_GET;
        $this->originalPostVars = $_POST;
        $this->originalServerSettings = $_SERVER;

        $this->restApiRequestScopeMock = $this->createMock(RestApiRequestScope::class);
        $typo3CacheMock = $this->createMock(Typo3Cache::class);

        $this->restApiRequestMock = $this->getMockBuilder(RestApiRequest::class)
            ->setConstructorArgs([$this->restApiRequestScopeMock, $typo3CacheMock])
            ->onlyMethods(['handle'])
            ->getMock();
    }

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

        $originalRestApiRequest = $this->createMock(Restler::class);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('storeOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('storeOriginalRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('overrideOriginalRestApiRequest')
            ->with($this->restApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willReturn($result);
        $this->assertSame($result, $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     */
    public function testShouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestIsDone(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $result = ['id' => 1, 'name' => 'Test-Product'];

        $originalRestApiRequest = $this->createMock(Restler::class);
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('overrideOriginalRestApiRequest')
            ->with($this->restApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willReturn($result);
        $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri);

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

        $originalRestApiRequest = $this->createMock(Restler::class);
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('overrideOriginalRestApiRequest')
            ->with($this->restApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        try {
            $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri);
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

        $originalRestApiRequest = $this->createMock(Restler::class);
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('overrideOriginalRestApiRequest')
            ->with($this->restApiRequestMock);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        try {
            $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri);
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

        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $originalRestApiRequest = $this->createMock(Restler::class);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);

        $this->expectException(RestException::class);
        $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
    }

    public function testShouldThrowRestExceptionWhenExecutionOfRestApiRequestFailsWithRestException(): void
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData = ['context' => 'mobile'];
        $postData = [];
        $exception = new RestException(400, 'message');

        $this->restApiRequestMock->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $originalRestApiRequest = $this->createMock(Restler::class);
        $this->restApiRequestScopeMock->expects($this->once())
            ->method('getOriginalRestApiRequest')
            ->willReturn($originalRestApiRequest);

        $this->expectException(RestException::class);
        $this->restApiRequestMock->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
    }
}
