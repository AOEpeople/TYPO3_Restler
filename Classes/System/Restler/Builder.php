<?php
namespace Aoe\Restler\System\Restler;

use Aoe\Restler\Configuration\ExtensionConfiguration;
use InvalidArgumentException;
use Luracast\Restler\Defaults;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
 */
class Builder implements SingletonInterface
{
    /**
     * @var ExtensionConfiguration
     */
    private $extensionConfiguration;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration, ObjectManagerInterface $objectManager)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->objectManager = $objectManager;
    }

    /**
     * initialize and configure restler-framework and return restler-object
     *
     * @return Restler
     */
    public function build()
    {
        $this->setAutoLoading();
        $this->setCacheDirectory();
        $this->setServerConfiguration();

        $restlerObj = $this->createRestlerObject();
        $this->configureRestler($restlerObj);
        return $restlerObj;
    }

    /**
     * @return Restler
     */
    protected function createRestlerObject()
    {
        return new Restler(
            $this->extensionConfiguration->isProductionContextSet(),
            $this->extensionConfiguration->isCacheRefreshingEnabled()
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
     * @param Restler $restler
     * @throws InvalidArgumentException
     */
    private function configureRestler(Restler $restler)
    {
        $restlerConfigurationClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restler']['restlerConfigurationClasses'];

        if (false === is_array($restlerConfigurationClasses) || count($restlerConfigurationClasses) === 0) {
            $message = 'No restler-configuration-class found (at least one restler-configuration-class is required)! ';
            $message.= 'The configuration-class must be registered in ext_localconf.php of your TYPO3-extension like this: ';
            $message.= '$GLOBALS[\'TYPO3_CONF_VARS\'][\'SC_OPTIONS\'][\'restler\'][\'restlerConfigurationClasses\'][] = \'[YourConfigurationClass]\';';
            $message.= 'The configuration-class must implement this interface: Aoe\Restler\System\Restler\ConfigurationInterface';
            throw new InvalidArgumentException($message);
        }

        foreach($restlerConfigurationClasses as $restlerConfigurationClass) {
            $configurationObj = $this->objectManager->get($restlerConfigurationClass);

            /* @var $configurationObj ConfigurationInterface */
            if (false === $configurationObj instanceof ConfigurationInterface) {
                $message = 'class "' . $restlerConfigurationClass . '" did not implement the ';
                $message.= 'interface "Aoe\Restler\System\Restler\ConfigurationInterface"!';
                throw new InvalidArgumentException($message);
            }

            $configurationObj->configureRestler($restler);
        }
    }

    /**
     * use auto-loading for PHP-classes of restler-framework and extBase/TYPO3 (use dependency-injection of extBase)
     */
    private function setAutoLoading()
    {
        // set auto-loading for restler
        $autoload = PATH_site . 'typo3conf/ext/restler/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        // set auto-loading for extBase/TYPO3-classes
        $objectManager = $this->objectManager;
        Scope::$resolver = function ($className) use ($objectManager) {
            return $objectManager->get($className);
        };
    }

    /**
     * configure cache-directory (where restler can write cache-files)
     */
    private function setCacheDirectory()
    {
        Defaults::$cacheDirectory = PATH_site . 'typo3temp/tx_restler';
    }

    /**
     * fix server-port (if not correct set)
     *
     * @return Restler
     */
    private function setServerConfiguration()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' && $_SERVER['SERVER_PORT'] === '80') {
            // Fix port for HTTPS
            // Otherwise restler will create those urls for online-documentation, when HTTPS is used: https://www.myDomain.de:80
            $_SERVER['SERVER_PORT'] = '443';
        }
    }
}
