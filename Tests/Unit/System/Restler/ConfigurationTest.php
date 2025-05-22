<?php

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

/**
 * @package Restler
 * @subpackage Tests
 */
class ConfigurationTest extends BaseTestCase
{
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfigurationMock;

    /**
     * @var Restler
     */
    protected $restlerMock;

    /**
     * @var Configuration
     */
    protected $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->getMockBuilder(ExtensionConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restlerMock = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();
        $this->configuration = new Configuration($this->extensionConfigurationMock);
    }

    public function testCheckThatCorrectClassesWillAddedWhenOnlineDocumentationIsEnabled(): void
    {
        $this->extensionConfigurationMock->expects(self::once())->method('isOnlineDocumentationEnabled')->willReturn(true);
        $this->extensionConfigurationMock->expects(self::once())->method('getPathOfOnlineDocumentation')->willReturn('path');

        $this->restlerMock
            ->expects(self::once())
            ->method('addAPIClass')
            ->with(Explorer::class, 'path');

        $this->restlerMock
            ->expects(self::exactly(3))
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
        $this->extensionConfigurationMock->expects(self::once())->method('isOnlineDocumentationEnabled')->willReturn(false);
        $this->extensionConfigurationMock->expects(self::never())->method('getPathOfOnlineDocumentation');

        $this->restlerMock
            ->expects(self::never())
            ->method('addAPIClass');

        $this->restlerMock
            ->expects(self::exactly(2))
            ->method('addAuthenticationClass')
            ->willReturnOnConsecutiveCalls(
                [BeUserAuthenticationController::class],
                [FeUserAuthenticationController::class]
            );

        $this->configuration->configureRestler($this->restlerMock);
    }
}
