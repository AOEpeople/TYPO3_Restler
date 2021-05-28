<?php
namespace Aoe\Restler\Tests\Unit\Configuration;

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
use Aoe\Restler\Tests\Unit\BaseTest;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\Configuration\ExtensionConfiguration
 */
class ExtensionConfigurationTest extends BaseTest
{
    /**
     * @var ExtensionConfiguration
     */
    protected $configuration;

    /**
     * setup
     */
    protected function setUp()
    {
        $mockedExtConfig = [
            'refreshCache' => '0',
            'productionContext' => '1',
            'enableOnlineDocumentation' => '1',
            'pathToOnlineDocumentation' => 'api_explorer'
        ];

        $typo3ExtensionConfiguration = $this->getMockBuilder(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typo3ExtensionConfiguration->expects(self::once())->method('get')->with('restler')->willReturn($mockedExtConfig);

        $this->configuration = new ExtensionConfiguration($typo3ExtensionConfiguration);
    }

    /**
     * @test
     */
    public function canCheckThatCacheRefreshingIsNotEnabled()
    {
        self::assertFalse($this->configuration->isCacheRefreshingEnabled());
    }

    /**
     * @test
     */
    public function canCheckThatProductionContextIsSet()
    {
        self::assertTrue($this->configuration->isProductionContextSet());
    }

    /**
     * @test
     */
    public function canCheckThatOnlineDocumentationIsEnabled()
    {
        self::assertTrue($this->configuration->isOnlineDocumentationEnabled());
    }

    /**
     * @test
     */
    public function canGetPathOfOnlineDocumentation()
    {
        self::assertEquals('api_explorer', $this->configuration->getPathOfOnlineDocumentation());
    }
}
