<?php

declare(strict_types=1);

namespace Aoe\Restler\Tests\Unit\System\Restler;

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
use Aoe\Restler\Controller\BeUserAuthenticationController;
use Aoe\Restler\Controller\ExplorerAuthenticationController;
use Aoe\Restler\Controller\FeUserAuthenticationController;
use Aoe\Restler\System\Restler\Configuration;
use Aoe\Restler\Tests\Unit\BaseTestCase;
use Luracast\Restler\Explorer\v2\Explorer;
use Luracast\Restler\Restler;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @package Restler
 * @subpackage Tests
 */
final class ConfigurationTest extends BaseTestCase
{
    private Configuration $configuration;

    private ExtensionConfiguration&MockObject $extensionConfigurationMock;

    private Restler&MockObject $restlerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $this->restlerMock = $this->createMock(Restler::class);
        $this->configuration = new Configuration($this->extensionConfigurationMock);
    }

    public function testCheckThatCorrectClassesWillAddedWhenOnlineDocumentationIsEnabled(): void
    {
        $this->extensionConfigurationMock->expects($this->once())
            ->method('isOnlineDocumentationEnabled')
            ->willReturn(true);
        $this->extensionConfigurationMock->expects($this->once())
            ->method('getPathOfOnlineDocumentation')
            ->willReturn('path');

        $this->restlerMock
            ->expects($this->once())
            ->method('addAPIClass')
            ->with(Explorer::class, 'path');

        $this->restlerMock
            ->expects($this->exactly(3))
            ->method('addAuthenticationClass')
            ->willReturnOnConsecutiveCalls(
                [ExplorerAuthenticationController::class],
                [BeUserAuthenticationController::class],
                [FeUserAuthenticationController::class]
            );

        $this->configuration->configureRestler($this->restlerMock);
    }

    public function testCheckThatCorrectClassesWillAddedWhenOnlineDocumentationIsNotEnabled(): void
    {
        $this->extensionConfigurationMock->expects($this->once())
            ->method('isOnlineDocumentationEnabled')
            ->willReturn(false);
        $this->extensionConfigurationMock->expects($this->never())
            ->method('getPathOfOnlineDocumentation');

        $this->restlerMock
            ->expects($this->never())
            ->method('addAPIClass');

        $this->restlerMock
            ->expects($this->exactly(2))
            ->method('addAuthenticationClass')
            ->willReturnOnConsecutiveCalls(
                [BeUserAuthenticationController::class],
                [FeUserAuthenticationController::class]
            );

        $this->configuration->configureRestler($this->restlerMock);
    }
}
