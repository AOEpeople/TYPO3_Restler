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

use Aoe\Restler\System\RestApi\RestApiRequest;
use Aoe\Restler\System\RestApi\RestApiRequestScope;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\RestException;
use Exception;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\RestApi\RestApiRequest
 */
class RestApiRequestTest extends BaseTest
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
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        // store the global data ($_GET, $_POST, $_SERVER)
        $this->originalGetVars = $_GET;
        $this->originalPostVars = $_POST;
        $this->originalServerSettings = $_SERVER;

        $this->restApiRequestScopeMock = $this->getMockBuilder('Aoe\\Restler\\System\\RestApi\\RestApiRequestScope')
            ->disableOriginalConstructor()->getMock();

        $this->restApiRequest = $this->getMockBuilder('Aoe\\Restler\\System\\RestApi\\RestApiRequest')
            ->setConstructorArgs(array($this->restApiRequestScopeMock))
            ->setMethods(array('handle'))
            ->getMock();
    }

    /**
     * tear down
     */
    protected function tearDown()
    {
        // restore the global data ($_GET, $_POST, $_SERVER)
        $_GET = $this->originalGetVars;
        $_POST = $this->originalPostVars;
        $_SERVER = $this->originalServerSettings;

        parent::tearDown();
    }

    /**
     * @test
     */
    public function canExecuteRestApiRequest()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $result = array('id' => 1, 'name' => 'Test-Product');

        $originalRestApiRequest = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $this->restApiRequestScopeMock->expects($this->once())->method('storeOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())->method('storeOriginalRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects($this->once())->method('handle')->willReturn($result);
        $this->assertEquals($result, $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData));
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     *
     * @test
     */
    public function shouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestIsDone()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $result = array('id' => 1, 'name' => 'Test-Product');

        $originalRestApiRequest = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects($this->once())->method('handle')->willReturn($result);
        $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     *
     * @test
     */
    public function shouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestFailsWithException()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $exception = new Exception();

        $originalRestApiRequest = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects($this->once())->method('handle')->will($this->throwException($exception));

        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);
        } catch (Exception $e) {
        }

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    /**
     * Test, that the restApiRequest-object restore the original data of $_GET, $_POST and $_SERVER and the original restApiRequest-object
     *
     * @test
     */
    public function shouldRestoreOriginalRestApiRequestWhenExecutionOfRestApiRequestFailsWithRestException()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $exception = new RestException(400, 'message');

        $originalRestApiRequest = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        // DO NOT check, if method 'storeOriginalRestApiRequest' of object 'restApiRequestScopeMock' was called:
        // This method is ONLY called ONCE A TIME (to be sure, that REALY only the 'original' data will be stored)!
        $this->restApiRequestScopeMock->expects($this->once())->method('overrideOriginalRestApiRequest')->with($this->restApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('removeRestApiAuthenticationObjects');
        $this->restApiRequestScopeMock->expects($this->once())->method('getOriginalRestApiRequest')->willReturn($originalRestApiRequest);
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiRequest');
        $this->restApiRequestScopeMock->expects($this->once())->method('restoreOriginalRestApiAuthenticationObjects');
        $this->restApiRequest->expects($this->once())->method('handle')->will($this->throwException($exception));

        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri);
        } catch (Exception $e) {
        }

        $this->assertEquals($this->originalGetVars, $_GET);
        $this->assertEquals($this->originalPostVars, $_POST);
        $this->assertEquals($this->originalServerSettings, $_SERVER);
    }

    /**
     * @test
     */
    public function shouldThrowRestExceptionWhenExecutionOfRestApiRequestFailsWithException()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $exception = new Exception();

        $this->restApiRequest->expects($this->once())->method('handle')->will($this->throwException($exception));

        $thrownException = null;
        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
        } catch (Exception $e) {
            $thrownException = $e;
        }
        $this->assertInstanceOf('Luracast\Restler\RestException', $thrownException);
    }

    /**
     * @test
     */
    public function shouldThrowRestExceptionWhenExecutionOfRestApiRequestFailsWithRestException()
    {
        $requestMethod = 'GET';
        $requestUri = '/api/products/1';
        $getData  = array('context' => 'mobile');
        $postData = array();
        $exception = new RestException(400, 'message');

        $this->restApiRequest->expects($this->once())->method('handle')->will($this->throwException($exception));

        $thrownException = null;
        try {
            $this->restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
        } catch (Exception $e) {
            $thrownException = $e;
        }
        $this->assertInstanceOf('Luracast\Restler\RestException', $thrownException);
    }
}
