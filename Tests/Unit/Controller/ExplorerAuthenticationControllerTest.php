<?php

namespace Aoe\Restler\Tests\Unit\Controller;

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

use Aoe\Restler\Controller\ExplorerAuthenticationController;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Luracast\Restler\Data\ApiMethodInfo;
use Luracast\Restler\Explorer\v2\Explorer;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\Controller\ExplorerAuthenticationController
 */
class ExplorerAuthenticationControllerTest extends BaseTestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiMethodInfoMock = new ApiMethodInfo();

        /** @var \Luracast\Restler\Restler $restlerMock */
        $restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerMock->apiMethodInfo = $this->apiMethodInfoMock;

        $this->controller = new ExplorerAuthenticationController();
        $this->controller->restler = $restlerMock;
    }

    public function testCheckThatAuthenticationWillFail(): void
    {
        $this->apiMethodInfoMock->className = 'NoneExplorerClass';
        $this->assertFalse($this->controller->__isAllowed());
    }

    public function testCheckThatAuthenticationWillBeSuccessful(): void
    {
        $this->apiMethodInfoMock->className = Explorer::class;
        $this->assertTrue($this->controller->__isAllowed());
    }

    public function testCheckForCorrectAuthenticationString(): void
    {
        $this->apiMethodInfoMock->className = Explorer::class;
        $this->assertSame('Query name="api_key"', $this->controller->__getWWWAuthenticateString());
    }
}
