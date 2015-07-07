<?php
namespace Aoe\Restler\Tests\Unit\Controller;

use Aoe\Restler\Controller\FeUserAuthenticationController;
use Aoe\Restler\System\TYPO3\Loader as TYPO3Loader;
use Aoe\Restler\Tests\Unit\BaseTest;

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
     * @var array
     */
    protected $originalGlobalVars;
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

        $this->originalGlobalVars = $GLOBALS;

        $this->typo3LoaderMock = $this->getMockBuilder('Aoe\\Restler\\System\\TYPO3\\Loader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = $this->objectManager->get('Aoe\\Restler\\Controller\\FeUserAuthenticationController');
        $this->inject($this->controller, 'typo3Loader', $this->typo3LoaderMock);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $GLOBALS = $this->originalGlobalVars;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenControllerIsNotResponsibleForAuthenticationCheck()
    {
        $this->typo3LoaderMock->expects($this->never())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects($this->never())->method('getFrontEndUser');
        $this->assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenFeUserIsNotLoggedIn()
    {
        $this->controller->checkAuthentication = true;

        $feUser = $this->createMockedFrontEndUser();
        $feUser->user = null;
        $this->typo3LoaderMock->expects($this->once())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects($this->once())->method('getFrontEndUser')->will($this->returnValue($feUser));

        $this->assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillBeSuccessful()
    {
        $this->controller->checkAuthentication = true;

        $feUser = $this->createMockedFrontEndUser();
        $feUser->user = array('username' => 'max.mustermann');
        $this->typo3LoaderMock->expects($this->once())->method('initializeFrontEndUser');
        $this->typo3LoaderMock->expects($this->once())->method('getFrontEndUser')->will($this->returnValue($feUser));

        $this->assertTrue($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function shouldSetPageIdZeroIfArgumentDoesNotExist()
    {
        /** @var \Luracast\Restler\Data\ApiMethodInfo $apiMethodInfoMock */
        $apiMethodInfoMock = $this->getMockBuilder('Luracast\\Restler\\Data\\ApiMethodInfo')->disableOriginalConstructor()->getMock();

        /* @var $restlerMock \Luracast\Restler\Restler */
        $restlerMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        $this->assertEquals(0, $method->invoke($this->controller));
    }

    /**
     * @test
     */
    public function shouldSetPageIdIfArgumentDoesExist()
    {
        /** @var \Luracast\Restler\Data\ApiMethodInfo $apiMethodInfoMock */
        $apiMethodInfoMock = $this->getMockBuilder('Luracast\\Restler\\Data\\ApiMethodInfo')->disableOriginalConstructor()->getMock();
        $apiMethodInfoMock->arguments = array_merge(
            $apiMethodInfoMock->arguments,
            array('pid' => 0)
        );
        $apiMethodInfoMock->parameters = array_merge(
            $apiMethodInfoMock->parameters,
            array(0 => 4711)
        );

        /* @var $restlerMock \Luracast\Restler\Restler */
        $restlerMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $apiMethodInfoMock;
        $this->inject($this->controller, 'restler', $restlerMock);

        $this->controller->checkAuthentication = true;
        $this->controller->argumentNameOfPageId = 'pid';

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('determinePageIdFromArguments');
        $method->setAccessible(true);

        $this->assertEquals(4711, $method->invoke($this->controller));
    }

    /**
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    private function createMockedFrontEndUser()
    {
        $feUser = $this->getMockBuilder('TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication')
            ->disableOriginalConstructor()->getMock();
        return $feUser;
    }

    /**
     * @test
     */
    public function checkForCorrectAuthenticationString()
    {
        $this->assertEquals('', $this->controller->__getWWWAuthenticateString());
    }
}
