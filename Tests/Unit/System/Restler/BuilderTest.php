<?php
namespace Aoe\Restler\Tests\Unit\System\Restler;

use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\Tests\Unit\BaseTest;
use Luracast\Restler\Defaults;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

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
 * @covers \Aoe\Restler\System\Restler\Builder
 */
class BuilderTest extends BaseTest
{
    /**
     * @var Builder
     */
    protected $builder;
    /**
     * original config of the restler-Extension
     * @var array
     */
    protected $originalRestlerConfigurationClasses;
    /**
     * original server-configuration ($_SERVER)
     * @var array
     */
    protected $originalServerVars;
    /**
     * @var Restler
     */
    protected $restlerMock;

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->originalRestlerConfigurationClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'];
        $this->originalServerVars = $_SERVER;

        // configure test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = array(
            'Aoe\\Restler\\Tests\\Unit\\System\\Restler\\Fixtures\\ValidConfiguration'
        );

        $this->restlerMock = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();

        $this->builder = $this->getMockBuilder('Aoe\\Restler\\System\\Restler\\Builder')
            ->setMethods(array('createRestlerObject'))
            ->setConstructorArgs(
                array(
                    $this->objectManager->get('Aoe\\Restler\\Configuration\\ExtensionConfiguration'),
                    $this->objectManager->get('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface')
                )
            )
            ->getMock();
        $this->builder->expects($this->once())->method('createRestlerObject')->will($this->returnValue($this->restlerMock));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = $this->originalRestlerConfigurationClasses;
        $_SERVER = $this->originalServerVars;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canBuildRestlerObject()
    {
        $this->restlerMock->expects($this->once())->method('addAPIClass')->with('Test-API-Class', 'Test-ResourcePath');
        $this->restlerMock->expects($this->once())->method('addAuthenticationClass')->with('Test-Authentication-Class');

        $restlerObj = $this->builder->build();
        $this->assertEquals($this->restlerMock, $restlerObj);
    }

    /**
     * @test
     */
    public function canSetAutoLoading()
    {
        Scope::$resolver = null;

        $this->builder->build();

        $closureToCreateObjectsViaObjectManager = Scope::$resolver;
        $this->assertTrue(is_callable($closureToCreateObjectsViaObjectManager));
        $createdObj = $closureToCreateObjectsViaObjectManager('Aoe\\Restler\\Configuration\\ExtensionConfiguration');
        $this->assertInstanceOf('Aoe\Restler\Configuration\ExtensionConfiguration', $createdObj);
    }

    /**
     * @test
     */
    public function canSetCacheDirectory()
    {
        Defaults::$cacheDirectory = '';

        $this->builder->build();
        $this->assertEquals(PATH_site . 'typo3temp/tx_restler', Defaults::$cacheDirectory);
    }

    /**
     * @test
     */
    public function canSetServerConfiguration()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '80';

        $this->builder->build();
        $this->assertEquals('443', $_SERVER['SERVER_PORT']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowExceptionWhenConfigurationOfRestlerClassesIsNoArray()
    {
        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = '';
        $this->builder->build();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowExceptionWhenConfigurationOfRestlerClassesIsEmptyArray()
    {
        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = array();
        $this->builder->build();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowExceptionWhenConfigurationOfRestlerClassDoesNotImplementRequiredInterface()
    {
        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = array(
            'Aoe\\Restler\\Tests\\Unit\\System\\Restler\\Fixtures\\InvalidConfiguration'
        );
        $this->builder->build();
    }
}
