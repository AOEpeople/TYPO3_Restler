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
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenControllerIsNotResponsibleForAuthenticationCheck()
    {
        $this->typo3LoaderMock->expects(self::never())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects(self::never())->method('getFrontEndUser');
        self::assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenFeUserIsNotLoggedIn()
    {
        $this->controller->checkAuthentication = true;

        $feUser = $this->createMockedFrontEndUser();
        $feUser->user = null;
        $this->typo3LoaderMock->expects(self::once())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects(self::once())->method('getFrontEndUser')->willReturn($feUser);

        self::assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillBeSuccessful()
    {
        $this->controller->checkAuthentication = true;

        $feUser = $this->createMockedFrontEndUser();
        $feUser->user = ['username' => 'max.mustermann'];
        $this->typo3LoaderMock->expects(self::once())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects(self::once())->method('getFrontEndUser')->willReturn($feUser);

        self::assertTrue($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function shouldSetPageIdZeroIfArgumentDoesNotExist()
    {
        /** @var ApiMethodInfo $apiMethodInfoMock */
        $apiMethodInfoMock = $this->getMockBuilder(ApiMethodInfo::class)->disableOriginalConstructor()->getMock();

        /* @var Restler $restlerMock */
        $restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        self::assertEquals(0, $method->invoke($this->controller));
    }

    /**
     * @test
     */
    public function shouldSetPageIdIfArgumentDoesExist()
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

        /* @var Restler $restlerMock */
        $restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        self::assertEquals(4711, $method->invoke($this->controller));
    }

    /**
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    private function createMockedFrontEndUser()
    {
        $feUser = $this->getMockBuilder(FrontendUserAuthentication::class)
            ->disableOriginalConstructor()->getMock();
        return $feUser;
    }

    /**
     * @test
     */
    public function checkForCorrectAuthenticationString()
    {
        self::assertEquals('', $this->controller->__getWWWAuthenticateString());
    }
}
