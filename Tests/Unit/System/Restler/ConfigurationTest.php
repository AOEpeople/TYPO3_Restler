<?php
namespace Aoe\Restler\Tests\Unit\System\Restler;

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

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Aoe\Restler\System\Restler\Configuration;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\Restler\Configuration
 */
class ConfigurationTest extends BaseTest
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

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extensionConfigurationMock = $this->getMockBuilder('Aoe\\Restler\\Configuration\\ExtensionConfiguration')
            ->disableOriginalConstructor()->getMock();
        $this->restlerMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();
        $this->configuration = new Configuration($this->extensionConfigurationMock);
    }

    /**
     * @test
     */
    public function checkThatCorrectClassesWillAddedWhenOnlineDocumentationIsEnabled()
    {
        $this->extensionConfigurationMock->expects($this->once())->method('isOnlineDocumentationEnabled')->will($this->returnValue(true));
        $this->extensionConfigurationMock->expects($this->once())->method('getPathOfOnlineDocumentation')->will($this->returnValue('path'));

        $this->restlerMock
            ->expects($this->at(0))
            ->method('addAPIClass')
            ->with('Luracast\\Restler\\Explorer', 'path');
        $this->restlerMock
            ->expects($this->at(1))
            ->method('addAuthenticationClass')
            ->with('Aoe\\Restler\\Controller\\ExplorerAuthenticationController');
        $this->restlerMock
            ->expects($this->at(2))
            ->method('addAuthenticationClass')
            ->with('Aoe\\Restler\\Controller\\BeUserAuthenticationController');
        $this->restlerMock
            ->expects($this->at(3))
            ->method('addAuthenticationClass')
            ->with('Aoe\\Restler\\Controller\\FeUserAuthenticationController');

        $this->configuration->configureRestler($this->restlerMock);
    }

    /**
     * @test
     */
    public function checkThatCorrectClassesWillAddedWhenOnlineDocumentationIsNotEnabled()
    {
        $this->extensionConfigurationMock->expects($this->once())->method('isOnlineDocumentationEnabled')->will(
            $this->returnValue(false)
        );
        $this->extensionConfigurationMock->expects($this->never())->method('getPathOfOnlineDocumentation');

        $this->restlerMock
            ->expects($this->never())
            ->method('addAPIClass');
        $this->restlerMock
            ->expects($this->at(0))
            ->method('addAuthenticationClass')
            ->with('Aoe\\Restler\\Controller\\BeUserAuthenticationController');
        $this->restlerMock
            ->expects($this->at(1))
            ->method('addAuthenticationClass')
            ->with('Aoe\\Restler\\Controller\\FeUserAuthenticationController');

        $this->configuration->configureRestler($this->restlerMock);
    }
}
