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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\System\Dispatcher;
use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Restler;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\Dispatcher
 */
class DispatcherTest extends BaseTest
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Builder
     */
    protected $restlerBuilder;

    /**
     * setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restlerBuilder = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->setMethods(['build'])->getMock();
        GeneralUtility::setSingletonInstance(Builder::class, $this->restlerBuilder);

        $configurationMock = self::getMockBuilder(ExtensionConfiguration::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = new Dispatcher($configurationMock);
    }

    /**
     * @test
     */
    public function canProcessToTypo3()
    {
        /** @var Restler|MockObject $restlerMock */
        $restlerMock = $this->createMock(Restler::class);
        $restlerMock->url = '/no/api/url';
        $this->restlerBuilder->expects(self::any())->method('build')->willReturn($restlerMock);

        $requestUri = $this->getMockBuilder(Uri::class)->getMock();
        $requestUri->method('getPath')->willReturn("/no/api/url");
        $requestUri->method('withQuery')->willReturn($requestUri);
        $requestUri->method('withPath')->willReturn($requestUri);

        $request = $this->getMockBuilder('Psr\\Http\\Message\\ServerRequestInterface')->getMock();
        $request->method('getUri')->willReturn($requestUri);

        $handler = $this->getMockBuilder('Psr\\Http\\Server\\RequestHandlerInterface')->getMock();
        $handler->expects(self::once())->method('handle');

        $this->dispatcher->process($request, $handler);
    }
}
