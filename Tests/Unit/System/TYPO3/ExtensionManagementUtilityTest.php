<?php
namespace Aoe\Restler\Tests\Unit\System\TYPO3;

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
use Aoe\Restler\System\TYPO3\ExtensionManagementUtility;
use Aoe\Restler\Tests\Unit\BaseTest;
use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\TYPO3\ExtensionManagementUtility
 */
class ExtensionManagementUtilityTest extends BaseTest
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;
    /**
     * @var ExtensionManagementUtility
     */
    protected $utility;
    /**
     * @var array
     */
    protected $savedConfigurationOfTYPO3loadedExtensions;

    /**
     * setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cacheManager = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\CacheManager')
            ->disableOriginalConstructor()->getMock();
        $this->extensionConfiguration = $this->getMockBuilder('Aoe\\Restler\\Configuration\\ExtensionConfiguration')
            ->disableOriginalConstructor()->getMock();
        $this->utility = new ExtensionManagementUtility($this->cacheManager, $this->extensionConfiguration);
        $this->savedConfigurationOfTYPO3loadedExtensions = $GLOBALS['TYPO3_LOADED_EXT'];
    }

    /**
     * tear down
     */
    protected function tearDown()
    {
        parent::tearDown();

        $GLOBALS['TYPO3_LOADED_EXT'] = $this->savedConfigurationOfTYPO3loadedExtensions;
    }

    /**
     * @test
     */
    public function canCheckThatExtensionHasToBeLoadedWhenNoneExtensionIsConfiguredToBeLoaded()
    {
        $extKey = 'extensionA';
        $requiredExtensions = array();
        $this->extensionConfiguration
            ->expects($this->once())->method('getExtensionsWithRequiredExtLocalConfFiles')
            ->will($this->returnValue($requiredExtensions));

        $hasToLoadExtension = $this->callUnaccessibleMethodOfObject($this->utility, 'hasToLoadExtension', array($extKey));
        $this->assertTrue($hasToLoadExtension);
    }

    /**
     * @test
     */
    public function canCheckThatExtensionHasToBeLoadedWhenExtensionIsConfiguredToBeLoaded()
    {
        $extKey = 'extensionA';
        $requiredExtensions = array($extKey);
        $this->extensionConfiguration
            ->expects($this->once())->method('getExtensionsWithRequiredExtLocalConfFiles')
            ->will($this->returnValue($requiredExtensions));

        $hasToLoadExtension = $this->callUnaccessibleMethodOfObject(
            $this->utility,
            'hasToLoadExtension',
            array($extKey)
        );
        $this->assertTrue($hasToLoadExtension);
    }

    /**
     * @test
     */
    public function canCheckThatExtensionHasNotToBeLoadedWhenExtensionIsNotConfiguredToBeLoaded()
    {
        $extKey = 'extensionA';
        $requiredExtensions = array('extensionB');
        $this->extensionConfiguration
            ->expects($this->once())->method('getExtensionsWithRequiredExtLocalConfFiles')
            ->will($this->returnValue($requiredExtensions));

        $hasToLoadExtension = $this->callUnaccessibleMethodOfObject(
            $this->utility,
            'hasToLoadExtension',
            array($extKey)
        );
        $this->assertFalse($hasToLoadExtension);
    }

    /**
     * @test
     */
    public function canGetCacheIdentifier()
    {
        $cacheIdentifier = $this->callUnaccessibleMethodOfObject($this->utility, 'getExtLocalconfCacheIdentifier');
        $this->assertTrue(stripos($cacheIdentifier, 'ext_localconf_rest_api_') === 0);
    }

    /**
     * @test
     */
    public function canGetInformationsOfRequiredExtensions()
    {
        $requiredExtensions = array('extensionA', 'extensionC');
        $this->extensionConfiguration
            ->expects($this->any())->method('getExtensionsWithRequiredExtLocalConfFiles')
            ->will($this->returnValue($requiredExtensions));

        $GLOBALS['TYPO3_LOADED_EXT'] = array();
        $GLOBALS['TYPO3_LOADED_EXT']['extensionA'] = array('ext_localconf.php' => 'pathToLocalconfFileOfExtensionA');
        $GLOBALS['TYPO3_LOADED_EXT']['extensionB'] = array('ext_localconf.php' => 'pathToLocalconfFileOfExtensionB');
        $GLOBALS['TYPO3_LOADED_EXT']['extensionC'] = array('ext_localconf.php' => 'pathToLocalconfFileOfExtensionC');

        $extensionInfos = $this->callUnaccessibleMethodOfObject($this->utility, 'getInformationsOfRequiredExtensions');
        $this->assertInternalType('array', $extensionInfos);
        $this->assertEquals(2, count($extensionInfos));
        $this->assertEquals($requiredExtensions, array_keys($extensionInfos));
        $this->assertEquals($GLOBALS['TYPO3_LOADED_EXT']['extensionA'], $extensionInfos['extensionA']);
        $this->assertEquals($GLOBALS['TYPO3_LOADED_EXT']['extensionC'], $extensionInfos['extensionC']);
    }

    /**
     * @test
     */
    public function canLoadExtLocalconfWhenCacheExists()
    {
        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\Frontend\\PhpFrontend')
            ->disableOriginalConstructor()->getMock();
        $cache->expects($this->once())->method('has')->will($this->returnValue(true));
        $cache->expects($this->once())->method('requireOnce');

        $this->cacheManager->expects($this->once())->method('getCache')->will($this->returnValue($cache));
        $this->utility->loadExtLocalconf();
    }

    /**
     * @test
     */
    public function canLoadExtLocalconfWhenCacheNotExists()
    {
        $GLOBALS['TYPO3_LOADED_EXT'] = array();

        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\Frontend\\PhpFrontend')
            ->disableOriginalConstructor()->getMock();
        $cache->expects($this->once())->method('has')->will($this->returnValue(false));
        $cache->expects($this->once())->method('set');
        $cache->expects($this->once())->method('requireOnce');

        $this->cacheManager->expects($this->once())->method('getCache')->will($this->returnValue($cache));
        $this->utility->loadExtLocalconf();
    }
}
