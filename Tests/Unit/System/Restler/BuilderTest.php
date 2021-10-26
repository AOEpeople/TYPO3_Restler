<?php
namespace Aoe\Restler\Tests\Unit\System\Restler;

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
use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\System\Restler\RestlerExtended;
use Aoe\Restler\System\TYPO3\Cache;
use Aoe\Restler\Tests\Unit\BaseTest;
use Aoe\Restler\Tests\Unit\System\Restler\Fixtures\InvalidConfiguration;
use Aoe\Restler\Tests\Unit\System\Restler\Fixtures\ValidConfiguration;
use InvalidArgumentException;
use Luracast\Restler\Defaults;
use Luracast\Restler\Scope;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     * @var ExtensionConfiguration|MockObject
     */
    protected $extensionConfigurationMock;
    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;
    /**
     * @var CacheManager|MockObject
     */
    protected $cacheManagerMock;

    /**
     * setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalRestlerConfigurationClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'];
        $this->originalServerVars = $_SERVER;

        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerMock);

        $this->extensionConfigurationMock = $this->getMockBuilder(ExtensionConfiguration::class)
            ->disableOriginalConstructor()->getMock();
        $this->cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCache'])
            ->getMock();

        $this->builder = new Builder(
            $this->extensionConfigurationMock,
            $this->cacheManagerMock
        );
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = $this->originalRestlerConfigurationClasses;
        $_SERVER = $this->originalServerVars;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canCreateRestlerObject()
    {
        $this->extensionConfigurationMock
            ->expects(self::once())->method('isProductionContextSet')
            ->willReturn(false);
        $this->extensionConfigurationMock
            ->expects(self::once())->method('isCacheRefreshingEnabled')
            ->willReturn(true);

        $typo3CacheMock = $this->getMockBuilder(Cache::class)->disableOriginalConstructor()->getMock();
        $this->objectManagerMock
            ->expects(self::once())->method('get')->with(Cache::class)
            ->willReturn($typo3CacheMock);

        $typo3RequestMock = $this->getMockBuilder(ServerRequest::class)->disableOriginalConstructor()->getMock();

        $requestUri = $this->getMockBuilder(Uri::class)->getMock();
        $requestUri->expects(self::atLeastOnce())->method('getPath')->willReturn("/api/device");
        $requestUri->method('withQuery')->willReturn($requestUri);
        $requestUri->method('withPath')->willReturn($requestUri);

        $typo3RequestMock->expects(self::atLeastOnce())->method('getUri')->willReturn($requestUri);

        $createdObj = $this->callUnaccessibleMethodOfObject($this->builder, 'createRestlerObject', [$typo3RequestMock] );
        self::assertInstanceOf(RestlerExtended::class, $createdObj);
    }

    /**
     * @test
     */
    public function canConfigureRestlerObject()
    {
        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();

        $configurationClass = ValidConfiguration::class;
        $configurationMock = $this->getMockBuilder($configurationClass)->disableOriginalConstructor()->getMock();
        $configurationMock->expects(self::once())->method('configureRestler')->with($restlerObj);

        $this->objectManagerMock->expects(self::once())->method('get')->with($configurationClass)->willReturn($configurationMock);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [$configurationClass];
        unset($GLOBALS['TYPO3_Restler']['restlerConfigurationClasses']);

        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    /**
     * @test
     */
    public function canConfigureRestlerWithExternalConfigurationClassObject()
    {
        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();

        $configurationClass = ValidConfiguration::class;
        $configurationMock = $this->getMockBuilder($configurationClass)->disableOriginalConstructor()->getMock();
        $configurationMock->expects(self::exactly(2))->method('configureRestler')->with($restlerObj);

        $this->objectManagerMock->expects(self::exactly(2))->method('get')->with($configurationClass)->willReturn($configurationMock);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [$configurationClass];
        $GLOBALS['TYPO3_Restler']['restlerConfigurationClasses'] = [$configurationClass];

        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    /**
     * @test
     */
    public function canNotConfigureRestlerObjectWhenConfigurationOfRestlerClassesIsNoArray()
    {
        $this->expectException(InvalidArgumentException::class);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = '';

        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();
        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    /**
     * @test
     */
    public function canNotConfigureRestlerObjectWhenConfigurationOfRestlerClassesIsEmptyArray()
    {
        $this->expectException(InvalidArgumentException::class);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [];

        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();
        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function canNotConfigureRestlerObjectWhenConfigurationOfRestlerClassDoesNotImplementRequiredInterface()
    {
        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();

        $configurationClass = InvalidConfiguration::class;
        $configurationMock = $this->getMockBuilder($configurationClass)->disableOriginalConstructor()->getMock();

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [$configurationClass];

        $this->objectManagerMock
            ->expects(self::once())->method('get')->with($configurationClass)
            ->willReturn($configurationMock);

        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    /**
     * @test
     */
    public function canSetAutoLoading()
    {
        // set object-property (which the builder should update)
        Scope::$resolver = null;

        $requestedClass = ExtensionConfiguration::class;
        $this->objectManagerMock
            ->expects(self::once())->method('get')->with($requestedClass)
            ->willReturn($this->extensionConfigurationMock);

        $this->callUnaccessibleMethodOfObject($this->builder, 'setAutoLoading');

        $closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance = Scope::$resolver;
        self::assertTrue(is_callable($closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance));
        $createdObj = $closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance($requestedClass);
        self::assertEquals($createdObj, $this->extensionConfigurationMock);
    }

    /**
     * @test
     */
    public function canSetCacheDirectory()
    {
        // set object-property (which the builder should update)
        Defaults::$cacheDirectory = '';

        /** @var SimpleFileBackend|MockObject $simpleFileBackend */
        $simpleFileBackend = $this->getMockBuilder(SimpleFileBackend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCacheDirectory'])
            ->getMock();
        $simpleFileBackend->expects(self::once())->method('getCacheDirectory')->willReturn(
            'typo3temp/Cache/Data/tx_restler_cache'
        );

        /** @var PhpFrontend|MockObject $simpleFileBackend */
        $cacheFrontend = $this->getMockBuilder(PhpFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBackend'])
            ->getMock();
        $cacheFrontend->expects(self::once())->method('getBackend')->willReturn($simpleFileBackend);

        $this->cacheManagerMock->expects(self::once())->method('getCache')->willReturn($cacheFrontend);

        $this->callUnaccessibleMethodOfObject($this->builder, 'setCacheDirectory');
        self::assertEquals('typo3temp/Cache/Data/tx_restler_cache', Defaults::$cacheDirectory);
    }

    /**
     * @test
     */
    public function canSetServerConfiguration()
    {
        // set variables (which the builder should use/update)
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '80';

        $this->callUnaccessibleMethodOfObject($this->builder, 'setServerConfiguration');
        self::assertEquals('443', $_SERVER['SERVER_PORT']);
    }

    /**
     * @test
     */
    public function addApiControllerClassesFromLocalConf()
    {
        // setup
        $backupGlobals = $GLOBALS['TYPO3_Restler']['addApiClass'];
        unset($GLOBALS['TYPO3_Restler']['addApiClass']);
        $GLOBALS['TYPO3_Restler']['addApiClass']['foopath'][] = 'BarController';

        $restlerObj = $this->getMockBuilder(RestlerExtended::class)->disableOriginalConstructor()->getMock();
        $restlerObj->expects(self::once())->method('addAPIClass')->with('BarController', 'foopath');

        //verifiy
        $this->callUnaccessibleMethodOfObject($this->builder, 'addApiClassesByGlobalArray', array($restlerObj));

        //tear down
        $GLOBALS['TYPO3_Restler']['addApiClass'] = $backupGlobals;
    }
}
