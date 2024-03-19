<?php

namespace Aoe\Restler\Tests\Unit\Controller;

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

use Aoe\Restler\Controller\FeUserAuthenticationController;
use Aoe\Restler\System\TYPO3\Loader as TYPO3Loader;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Data\ApiMethodInfo;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\Controller\FeUserAuthenticationController
 */
class FeUserAuthenticationControllerTest extends BaseTest
{
    /**
     * @var FeUserAuthenticationController
     */
    protected $controller;

    /**
     * @var TYPO3Loader
     */
    protected $typo3LoaderMock;

    /**
     * setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->typo3LoaderMock = $this->getMockBuilder(TYPO3Loader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new FeUserAuthenticationController($this->typo3LoaderMock);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testCheckThatAuthenticationWillFailWhenControllerIsNotResponsibleForAuthenticationCheck(): void
    {
        $this->typo3LoaderMock->expects(self::never())->method('initializeFrontendUser');
        $this->typo3LoaderMock->expects(self::never())->method('hasActiveFrontendUser');
        $this->assertFalse($this->controller->__isAllowed());
    }

    public function testCheckThatAuthenticationWillFailWhenFeUserIsNotLoggedIn(): void
    {
        $this->controller->argumentNameOfPageId = '5';
        $this->controller->checkAuthentication = true;

        $this->typo3LoaderMock->expects(self::once())->method('initializeFrontendUser')->with('5');
        $this->typo3LoaderMock->expects(self::once())->method('hasActiveFrontendUser')->willReturn(false);

        $this->assertFalse($this->controller->__isAllowed());
    }

    public function testCheckThatAuthenticationWillBeSuccessful(): void
    {
        $this->controller->argumentNameOfPageId = '5';
        $this->controller->checkAuthentication = true;

        // determinePageId should determine page id from class var argumentNameOfPageId
        $this->typo3LoaderMock->expects(self::once())->method('initializeFrontendUser')->with('5');
        $this->typo3LoaderMock->expects(self::once())->method('hasActiveFrontendUser')->willReturn(true);

        $this->assertTrue($this->controller->__isAllowed());
    }

    public function testShouldSetPageIdZeroIfArgumentDoesNotExist(): void
    {
        /** @var ApiMethodInfo $apiMethodInfoMock */
        $apiMethodInfoMock = $this->getMockBuilder(ApiMethodInfo::class)->disableOriginalConstructor()->getMock();

        /** @var Restler $restlerMock */
        $restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        $this->assertSame(0, $method->invoke($this->controller));
    }

    public function testShouldSetPageIdIfArgumentDoesExist(): void
    {
        /** @var \Luracast\Restler\Data\ApiMethodInfo $apiMethodInfoMock */
        $apiMethodInfoMock = $this->getMockBuilder(ApiMethodInfo::class)->disableOriginalConstructor()->getMock();
        $apiMethodInfoMock->arguments = array_merge(
            $apiMethodInfoMock->arguments,
            ['pid' => 0]
        );
        $apiMethodInfoMock->parameters = array_merge(
            $apiMethodInfoMock->parameters,
            [0 => 4711]
        );

        /** @var Restler $restlerMock */
        $restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        $this->assertSame(4711, $method->invoke($this->controller));
    }

    public function testCheckForCorrectAuthenticationString(): void
    {
        $this->assertSame('', $this->controller->__getWWWAuthenticateString());
    }
}
