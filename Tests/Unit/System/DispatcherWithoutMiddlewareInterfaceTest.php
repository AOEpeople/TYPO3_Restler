<?php

namespace Aoe\Restler\Tests\Unit\System;

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

use Aoe\Restler\System\DispatcherWithoutMiddlewareImplementation;
use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\DispatcherWithoutMiddlewareImplementation
 */
class DispatcherWithoutMiddlewareImplementationTest extends BaseTest
{
    /**
     * @var DispatcherWithoutMiddlewareImplementation
     */
    protected $dispatcher;
    /**
     * @var Builder
     */
    protected $restlerBuilder;
    /**
     * setup
     */
    protected function setUp()
    {
        if (!interface_exists('\Psr\Http\Server\MiddlewareInterface')) {

            parent::setUp();
            $this->restlerBuilder = $this->getMockBuilder(Builder::class)
                ->disableOriginalConstructor()->getMock();

            $this->dispatcher = new DispatcherWithoutMiddlewareImplementation($this->restlerBuilder);

        } else {
            self::markTestSkipped("Outdated if MiddlewareInterface is available (TYPO3 > 8.7)");
        }
    }

    /**
     * @test
     */
    public function canDispatch()
    {
        $restlerObj = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $restlerObj->expects(self::once())->method('handle');
        $this->restlerBuilder->expects(self::once())->method('build')->willReturn($restlerObj);
        $this->dispatcher->dispatch();
    }
}
