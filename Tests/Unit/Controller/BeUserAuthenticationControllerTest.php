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

use Aoe\Restler\Controller\BeUserAuthenticationController;
use Aoe\Restler\System\TYPO3\Loader as TYPO3Loader;
use Aoe\Restler\Tests\Unit\BaseTest;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\Controller\BeUserAuthenticationController
 */
class BeUserAuthenticationControllerTest extends BaseTest
{
    /**
     * @var BeUserAuthenticationController
     */
    protected $controller;
    /**
     * @var TYPO3Loader
     */
    protected $typo3LoaderMock;

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->typo3LoaderMock = $this->getMockBuilder(TYPO3Loader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new BeUserAuthenticationController($this->typo3LoaderMock);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenControllerIsNotResponsibleForAuthenticationCheck()
    {
        $this->typo3LoaderMock->expects(self::never())->method('hasActiveBackendUser');
        self::assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenBackendUserIsNotLoggedIn()
    {
        $this->controller->checkAuthentication = true;

        $this->typo3LoaderMock->expects(self::once())->method('hasActiveBackendUser')->willReturn(false);

        self::assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillBeSuccessful()
    {
        $this->controller->checkAuthentication = true;

        $beUser = $this->getMockBuilder(BackendUserAuthentication::class)->disableOriginalConstructor()->getMock();
        $beUser->user = [
            'uid' => 1
        ];

        $this->typo3LoaderMock->expects(self::once())->method('hasActiveBackendUser')->willReturn(true);

        self::assertTrue($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkForCorrectAuthenticationString()
    {
        self::assertEquals('', $this->controller->__getWWWAuthenticateString());
    }
}
