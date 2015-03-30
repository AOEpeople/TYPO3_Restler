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

        $this->typo3LoaderMock = $this->getMockBuilder('Aoe\\Restler\\System\\TYPO3\\Loader')->disableOriginalConstructor()->getMock();
        $this->controller = $this->objectManager->get('Aoe\\Restler\\Controller\\FeUserAuthenticationController');
        $this->inject($this->controller, 'typo3Loader', $this->typo3LoaderMock);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $GLOBALS = $this->originalGlobalVars;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenControllerIsNotResponsibleForAuthenticationCheck()
    {
        $this->typo3LoaderMock->expects($this->never())->method('initializeFrontEndUser');
        $this->assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFailWhenFeUserIsNotLoggedIn()
    {
        $this->controller->checkAuthentication = true;

        $GLOBALS['TSFE'] = $this->createMockedTsfe();
        $GLOBALS['TSFE']->fe_user->user = null;

        $this->typo3LoaderMock->expects($this->once())->method('initializeFrontEndUser');
        $this->assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillBeSuccessful()
    {
        $this->controller->checkAuthentication = true;

        $GLOBALS['TSFE'] = $this->createMockedTsfe();
        $GLOBALS['TSFE']->fe_user->user = array('username' => 'max.mustermann');

        $this->typo3LoaderMock->expects($this->once())->method('initializeFrontEndUser');
        $this->assertTrue($this->controller->__isAllowed());
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    private function createMockedTsfe()
    {
        /* @var $tsfe \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
        $tsfe = $this->getMockBuilder('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController')
            ->disableOriginalConstructor()->getMock();
        /* @var $feUser \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication */
        $feUser = $this->getMockBuilder('TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication')
            ->disableOriginalConstructor()->getMock();
        $tsfe->fe_user = $feUser;
        return $tsfe;
    }
}
