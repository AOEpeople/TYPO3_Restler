<?php
namespace Aoe\Restler\System\TYPO3;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * @package Restler
 *
 * @codeCoverageIgnore
 */
class ExtensionManagementUtility
{
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var ExtensionConfiguration
     */
    private $extensionConfiguration;

    /**
     * @param CacheManager $cacheManager
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(CacheManager $cacheManager, ExtensionConfiguration $extensionConfiguration)
    {
        $this->cacheManager = $cacheManager;
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * Execute ext_localconf.php files of all loaded (and as required configured) extensions.
     * The method implements a caching mechanism that concatenates all ext_localconf.php files in one file.
     *
     * @return void
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadExtLocalconf
     */
    public function loadExtLocalconf()
    {
        $cacheIdentifier = $this->getExtLocalconfCacheIdentifier();
        /** @var $codeCache \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend */
        $codeCache = $this->cacheManager->getCache('cache_core');
        if (false === $codeCache->has($cacheIdentifier)) {
            $codeCache->set($cacheIdentifier, $this->createExtLocalconfCacheEntry());
        }
        $codeCache->requireOnce($cacheIdentifier);
    }

    /**
     * Create cache entry for concatenated ext_localconf.php files
     *
     * @return string
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::createExtLocalconfCacheEntry
     */
    private function createExtLocalconfCacheEntry()
    {
        $phpCodeToCache = array();
        // Set same globals as in loadSingleExtLocalconfFiles()
        $phpCodeToCache[] = '/**';
        $phpCodeToCache[] = ' * Compiled ext_localconf.php cache file';
        $phpCodeToCache[] = ' */';
        $phpCodeToCache[] = '';
        $phpCodeToCache[] = 'global $TYPO3_CONF_VARS, $T3_SERVICES, $T3_VAR;';
        $phpCodeToCache[] = '';
        // Iterate through loaded extensions and add ext_localconf content
        foreach ($this->getInformationsOfRequiredExtensions() as $extensionKey => $extensionInformation) {
            // Include a header per extension to make the cache file more readable
            $phpCodeToCache[] = '/**';
            $phpCodeToCache[] = ' * Extension: ' . $extensionKey;
            $phpCodeToCache[] = ' * File: ' . $extensionInformation['ext_localconf.php'];
            $phpCodeToCache[] = ' */';
            $phpCodeToCache[] = '';
            // Set $_EXTKEY and $_EXTCONF for this extension
            $phpCodeToCache[] = '$_EXTKEY = \'' . $extensionKey . '\';';
            $phpCodeToCache[] = '$_EXTCONF = $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXT\'][\'extConf\'][$_EXTKEY];';
            $phpCodeToCache[] = '';
            // Add ext_localconf.php content of extension
            $phpCodeToCache[] = trim(GeneralUtility::getUrl($extensionInformation['ext_localconf.php']));
            $phpCodeToCache[] = '';
            $phpCodeToCache[] = '';
        }
        $phpCodeToCache = implode(LF, $phpCodeToCache);
        // Remove all start and ending php tags from content
        $phpCodeToCache = preg_replace('/<\\?php|\\?>/is', '', $phpCodeToCache);
        return $phpCodeToCache;
    }

    /**
     * Cache identifier of concatenated ext_localconf file
     *
     * @return string
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtLocalconfCacheIdentifier
     */
    private function getExtLocalconfCacheIdentifier()
    {
        return 'ext_localconf_rest_api_' . sha1((TYPO3_version . PATH_site . 'extLocalconf'));
    }

    /**
     * @return array
     */
    private function getInformationsOfRequiredExtensions()
    {
        $extensionInformations = array();
        foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $extensionKey => $extensionInformation) {
            if (false === is_array($extensionInformation) && false === $extensionInformation instanceof \ArrayAccess) {
                continue;
            }
            if (false === isset($extensionInformation['ext_localconf.php'])) {
                continue;
            }
            if (false === $this->hasToLoadExtension($extensionKey)) {
                continue;
            }
            $extensionInformations[$extensionKey] = $extensionInformation;
        }
        return $extensionInformations;
    }

    /**
     * @param string $extKey
     * @return boolean
     */
    private function hasToLoadExtension($extKey)
    {
        $requiredExtensions = $this->extensionConfiguration->getExtensionsWithRequiredExtLocalConfFiles();
        if (count($requiredExtensions) === 0 || in_array($extKey, $requiredExtensions)) {
            return true;
        }
        return false;
    }
}
