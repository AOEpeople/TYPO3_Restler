<?php

declare(strict_types=1);

namespace Aoe\Restler\Tests\Unit\System;

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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\System\Dispatcher;
use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Luracast\Restler\Restler;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Restler
 * @subpackage Tests
 */
final class DispatcherTest extends BaseTestCase
{
    private Dispatcher $dispatcher;

    private Builder&MockObject $restlerBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restlerBuilder = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->onlyMethods(['build'])->getMock();
        GeneralUtility::setSingletonInstance(Builder::class, $this->restlerBuilder);

        $configurationMock = self::getMockBuilder(ExtensionConfiguration::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = new Dispatcher($configurationMock);
    }

    protected function tearDown(): void
    {
        $this->resetSingletonInstances = true;
        parent::tearDown();
    }

    public function testCanProcessToTypo3(): void
    {
        /** @var Restler|MockObject $restlerMock */
        $restlerMock = $this->createMock(Restler::class);
        $restlerMock->url = '/no/api/url';
        $this->restlerBuilder->method('build')
            ->willReturn($restlerMock);

        $requestUri = $this->createMock(Uri::class);
        $requestUri->method('getPath')
            ->willReturn('/no/api/url');
        $requestUri->method('withQuery')
            ->willReturn($requestUri);
        $requestUri->method('withPath')
            ->willReturn($requestUri);

        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->method('getUri')
            ->willReturn($requestUri);

        $handler = $this->createMock(\Psr\Http\Server\RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle');

        $this->dispatcher->process($request, $handler);
    }
}
