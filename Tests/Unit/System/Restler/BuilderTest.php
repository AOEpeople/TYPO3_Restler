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
use Aoe\Restler\System\Restler\Builder;
use Aoe\Restler\System\Restler\RestlerExtended;
use Aoe\Restler\System\TYPO3\Cache;
use Aoe\Restler\Tests\Unit\BaseTestCase;
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

/**
 * @package Restler
 * @subpackage Tests
 */
final class BuilderTest extends BaseTestCase
{
    private Builder $builder;

    private array $originalRestlerConfigurationClasses;

    /**
     * original server-configuration ($_SERVER)
     */
    private array $originalServerVars;

    private ExtensionConfiguration&MockObject $extensionConfigurationMock;

    private CacheManager&MockObject $cacheManagerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'] = [
            'restler' => [
                'restlerConfigurationClasses' => [],
            ],
        ];

        $GLOBALS['TYPO3_Restler'] = [
            'restlerConfigurationClasses' => [],
            'addApiClass' => null,
        ];

        $this->originalRestlerConfigurationClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'];
        $this->originalServerVars = $_SERVER;

        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $this->cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCache'])
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
        $this->resetSingletonInstances = true;
        parent::tearDown();
    }

    public function testCanCreateRestlerObject(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('isProductionContextSet')
            ->willReturn(false);
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('isCacheRefreshingEnabled')
            ->willReturn(true);

        $typo3CacheMock = $this->createMock(Cache::class);
        GeneralUtility::setSingletonInstance(Cache::class, $typo3CacheMock);

        $typo3RequestMock = $this->createMock(ServerRequest::class);

        $requestUri = $this->createMock(Uri::class);
        $requestUri->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn('/api/device');
        $requestUri->method('withQuery')
            ->willReturn($requestUri);
        $requestUri->method('withPath')
            ->willReturn($requestUri);

        $typo3RequestMock->expects($this->atLeastOnce())
            ->method('getUri')
            ->willReturn($requestUri);

        $createdObj = $this->callUnaccessibleMethodOfObject($this->builder, 'createRestlerObject', [$typo3RequestMock]);
        $this->assertInstanceOf(RestlerExtended::class, $createdObj);
    }

    public function testCanConfigureRestlerObject(): void
    {
        $restlerObj = $this->createMock(RestlerExtended::class);

        $configurationClass = ValidConfiguration::class;
        $configurationMock = $this->createMock($configurationClass);
        $configurationMock->expects($this->once())
            ->method('configureRestler')
            ->with($restlerObj);

        GeneralUtility::setSingletonInstance(ValidConfiguration::class, $configurationMock);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [$configurationClass];
        $GLOBALS['TYPO3_Restler']['restlerConfigurationClasses'] = [];

        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    public function testCanConfigureRestlerWithExternalConfigurationClassObject(): void
    {
        $restlerObj = $this->createMock(RestlerExtended::class);

        $configurationClass = ValidConfiguration::class;
        $configurationMock = $this->createMock($configurationClass);
        $configurationMock->expects($this->exactly(2))
            ->method('configureRestler')
            ->with($restlerObj);

        GeneralUtility::setSingletonInstance(ValidConfiguration::class, $configurationMock);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [$configurationClass];
        $GLOBALS['TYPO3_Restler']['restlerConfigurationClasses'] = [$configurationClass];

        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    public function testCanNotConfigureRestlerObjectWhenConfigurationOfRestlerClassesIsNoArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = '';

        $restlerObj = $this->createMock(RestlerExtended::class);
        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    public function testCanNotConfigureRestlerObjectWhenConfigurationOfRestlerClassesIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // override test-restler-configuration
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'] = [];

        $restlerObj = $this->createMock(RestlerExtended::class);
        $this->callUnaccessibleMethodOfObject($this->builder, 'configureRestler', [$restlerObj]);
    }

    public function testCanSetAutoLoading(): void
    {
        // set object-property (which the builder should update)
        Scope::$resolver = null;

        $requestedClass = ExtensionConfiguration::class;
        GeneralUtility::setSingletonInstance($requestedClass, $this->extensionConfigurationMock);

        $this->callUnaccessibleMethodOfObject($this->builder, 'setAutoLoading');

        $closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance = Scope::$resolver;
        $this->assertTrue(is_callable($closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance));
        $createdObj = $closureToCreateObjectsViaTypo3GeneralUtilityMakeInstance($requestedClass);
        $this->assertEquals($createdObj, $this->extensionConfigurationMock);
    }

    public function testCanSetCacheDirectory(): void
    {
        // set object-property (which the builder should update)
        Defaults::$cacheDirectory = '';

        /** @var SimpleFileBackend|MockObject $simpleFileBackend */
        $simpleFileBackend = $this->getMockBuilder(SimpleFileBackend::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCacheDirectory'])
            ->getMock();
        $simpleFileBackend->expects($this->once())
            ->method('getCacheDirectory')
            ->willReturn(
                'typo3temp/Cache/Data/tx_restler_cache'
            );

        $cacheFrontend = $this->getMockBuilder(PhpFrontend::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBackend'])
            ->getMock();
        $cacheFrontend->expects($this->once())
            ->method('getBackend')
            ->willReturn($simpleFileBackend);

        $this->cacheManagerMock->expects($this->once())
            ->method('getCache')
            ->willReturn($cacheFrontend);

        $this->callUnaccessibleMethodOfObject($this->builder, 'setCacheDirectory');
        $this->assertSame('typo3temp/Cache/Data/tx_restler_cache', Defaults::$cacheDirectory);
    }

    public function testCanSetServerConfiguration(): void
    {
        // set variables (which the builder should use/update)
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '80';

        $this->callUnaccessibleMethodOfObject($this->builder, 'setServerConfiguration');
        $this->assertSame('443', $_SERVER['SERVER_PORT']);
    }

    public function testAddApiControllerClassesFromLocalConf(): void
    {
        // setup
        $backupGlobals = $GLOBALS['TYPO3_Restler']['addApiClass'];
        unset($GLOBALS['TYPO3_Restler']['addApiClass']);
        $GLOBALS['TYPO3_Restler']['addApiClass']['foopath'][] = 'BarController';

        $restlerObj = $this->createMock(RestlerExtended::class);
        $restlerObj->expects($this->once())
            ->method('addAPIClass')
            ->with('BarController', 'foopath');

        //verifiy
        $this->callUnaccessibleMethodOfObject($this->builder, 'addApiClassesByGlobalArray', [$restlerObj]);

        //tear down
        $GLOBALS['TYPO3_Restler']['addApiClass'] = $backupGlobals;
    }
}
