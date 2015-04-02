<?php
namespace Aoe\Restler\Tests\Unit\Controller;

use Aoe\Restler\Controller\ExplorerAuthenticationController;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Data\ApiMethodInfo;

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
 * @covers \Aoe\Restler\Controller\ExplorerAuthenticationController
 */
class ExplorerAuthenticationControllerTest extends BaseTest
{
    /**
     * @var ApiMethodInfo
     */
    protected $apiMethodInfoMock;
    /**
     * @var ExplorerAuthenticationController
     */
    protected $controller;

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->apiMethodInfoMock = $this->getMockBuilder('Luracast\\Restler\\Data\\ApiMethodInfo')->disableOriginalConstructor()->getMock();

        /* @var $restlerMock \Luracast\Restler\Restler */
        $restlerMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $this->apiMethodInfoMock;

        $this->controller = $this->objectManager->get('Aoe\\Restler\\Controller\\ExplorerAuthenticationController');
        $this->inject($this->controller, 'restler', $restlerMock);
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillFail()
    {
        $this->apiMethodInfoMock->className = 'NoneExplorerClass';
        $this->assertFalse($this->controller->__isAllowed());
    }

    /**
     * @test
     */
    public function checkThatAuthenticationWillBeSuccessful()
    {
        $this->apiMethodInfoMock->className = 'Luracast\Restler\Explorer';
        $this->assertTrue($this->controller->__isAllowed());
    }
}
