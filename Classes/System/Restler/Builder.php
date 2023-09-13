<?php

namespace Aoe\Restler\System\Restler;

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
use Aoe\Restler\System\TYPO3\Cache;
use Error;
use InvalidArgumentException;
use Luracast\Restler\Defaults;
use Luracast\Restler\Scope;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @package Restler
 */
class Builder implements SingletonInterface
{
    private ExtensionConfiguration $extensionConfiguration;

    private CacheManager $cacheManager;

    public function __construct(ExtensionConfiguration $extensionConfiguration, CacheManager $cacheManager)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->cacheManager = $cacheManager;
    }

    /**
     * initialize and configure restler-framework and return restler-object
     *
     * @return RestlerExtended
     */
    public function build(ServerRequestInterface $request = null)
    {
        $this->setAutoLoading();
        $this->setCacheDirectory();
        $this->setServerConfiguration();

        $restlerObj = $this->createRestlerObject($request);
        $this->configureRestler($restlerObj);
        $this->addApiClassesByGlobalArray($restlerObj);
        return $restlerObj;
    }

    protected function createRestlerObject(ServerRequestInterface $request = null): RestlerExtended
    {
        return new RestlerExtended(
            GeneralUtility::makeInstance(Cache::class),
            $this->extensionConfiguration->isProductionContextSet(),
            $this->extensionConfiguration->isCacheRefreshingEnabled(),
            $request
        );
    }

    /**
     * Call all classes, which implements the interface 'Aoe\Restler\System\Restler\ConfigurationInterface'.
     * Those classes includes further restler-configurations, e.g.:
     *  - add API-classes
     *  - add authentication-classes
     *  - configure/set properties of several classes inside the restler-framework
     *  - configure overwriting of several classes inside the restler-framework
     *
     * @throws InvalidArgumentException
     */
    private function configureRestler(RestlerExtended $restler)
    {
        $restlerConfigurationClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'];

        if (!is_array($restlerConfigurationClasses) || $restlerConfigurationClasses === []) {
            $message = 'No restler-configuration-class found (at least one restler-configuration-class is required)! ';
            $message .= 'The configuration-class must be registered in ext_localconf.php of your TYPO3-extension like this: ';
            $message .= '$GLOBALS[\'TYPO3_CONF_VARS\'][\'SC_OPTIONS\'][\'restler\'][\'restlerConfigurationClasses\'][] =
                \'[YourConfigurationClass]\';';
            $message .= 'The configuration-class must implement this interface: Aoe\Restler\System\Restler\ConfigurationInterface';
            throw new InvalidArgumentException($message, 1428562059);
        }

        // append configuration classes from external GLOBAL registration
        if (isset($GLOBALS['TYPO3_Restler']['restlerConfigurationClasses']) && is_array($GLOBALS['TYPO3_Restler']['restlerConfigurationClasses'])) {
            $externalRestlerConfigurationClasses = array_unique($GLOBALS['TYPO3_Restler']['restlerConfigurationClasses']);
            $restlerConfigurationClasses = array_merge(
                $restlerConfigurationClasses,
                $externalRestlerConfigurationClasses
            );
        }

        foreach ($restlerConfigurationClasses as $restlerConfigurationClass) {
            /** @var ConfigurationInterface $configurationObj */
            $configurationObj = GeneralUtility::makeInstance($restlerConfigurationClass);

            if (!$configurationObj instanceof ConfigurationInterface) {
                $message = 'class "' . $restlerConfigurationClass . '" did not implement the ';
                $message .= 'interface "Aoe\Restler\System\Restler\ConfigurationInterface"!';
                throw new InvalidArgumentException($message, 1428562081);
            }

            $configurationObj->configureRestler($restler);
        }
    }

    /**
     * Add API-Controller-Classes that are registered by global array
     */
    private function addApiClassesByGlobalArray(RestlerExtended $restler)
    {
        if (array_key_exists('TYPO3_Restler', $GLOBALS) &&
            is_array($GLOBALS['TYPO3_Restler']) &&
            array_key_exists('addApiClass', $GLOBALS['TYPO3_Restler']) &&
            is_array($GLOBALS['TYPO3_Restler']['addApiClass'])) {
            foreach ($GLOBALS['TYPO3_Restler']['addApiClass'] as $apiEndpoint => $apiControllers) {
                $uniqueApiControllers = array_unique($apiControllers);
                foreach ($uniqueApiControllers as $apiController) {
                    $restler->addAPIClass($apiController, $apiEndpoint);
                }
            }
        }
    }

    /**
     * use auto-loading for PHP-classes of restler-framework and Extbase/TYPO3 (use dependency-injection of Extbase)
     */
    private function setAutoLoading()
    {
        // set auto-loading for Extbase/TYPO3-classes
        Scope::$resolver = function ($className) {
            try {
                return GeneralUtility::makeInstance($className);
            } catch (Error $error) {
                // @TODO TYPO3 v12:
                // Using of ObjectManager will be removed in TYPO3v12. Currently, we must use the ObjectManager
                // as a fallback because it can happen, that e.g. the REST-controllers (which 3rd-party-extensions
                // provide), are not supporting the new dependency-injection (via symfony) of TYPO3!

                // Log deprecation-notice
                $info = '%s should implement TYPO3\CMS\Core\SingletonInterface - otherwise the class can ';
                $info .= 'not be created by GeneralUtility::makeInstance() in TYPO3v12 (error-code: %s)!';
                trigger_error(
                    sprintf($info, $className, (string) $error->getCode()),
                    E_USER_DEPRECATED
                );

                // use legacy ObjectManager to create the required object
                return GeneralUtility::makeInstance(ObjectManager::class)->get($className);
            }
        };
    }

    /**
     * configure cache-directory (where restler can write cache-files)
     */
    private function setCacheDirectory()
    {
        Defaults::$cacheDirectory = $this->getCache()->getCacheDirectory();
    }

    /**
     * fix server-port (if not correct set)
     */
    private function setServerConfiguration()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' && $_SERVER['SERVER_PORT'] === '80') {
            // Fix port for HTTPS
            // Otherwise restler will create those urls for online-documentation, when HTTPS is used: https://www.example.com:80
            $_SERVER['SERVER_PORT'] = '443';
        }
    }

    /**
     * @return SimpleFileBackend
     * @throws NoSuchCacheException
     */
    private function getCache()
    {
        return $this->cacheManager->getCache('tx_restler_cache')
            ->getBackend();
    }
}
