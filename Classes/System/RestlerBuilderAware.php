<?php

namespace Aoe\Restler\System;

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
use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class RestlerBuilderAware
{
    /**
     * @var RestlerBuilder
     */
    private $restlerBuilder;

    /**
     * @var ExtensionConfiguration
     */
    private $extensionConfiguration;

    private $apiPrefix = '/api';

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * Get restlerBuilder on demand.
     *
     * @return RestlerBuilder
     */
    protected function getRestlerBuilder()
    {
        if ($this->restlerBuilder === null) {
            $this->restlerBuilder = GeneralUtility::makeInstance(RestlerBuilder::class);
        }
        return $this->restlerBuilder;
    }

    protected function isRestlerPrefix($prefixedUrlPath)
    {
        return $this->isRestlerApiUrl($prefixedUrlPath) || $this->isRestlerApiExplorerUrl($prefixedUrlPath);
    }

    protected function isRestlerApiUrl($prefixedUrlPath)
    {
        return $prefixedUrlPath === $this->apiPrefix || strpos($prefixedUrlPath, $this->apiPrefix . '/') === 0;
    }

    protected function isRestlerApiExplorerUrl($prefixedUrlPath)
    {
        $apiExplorerPrefix = '/' . $this->extensionConfiguration->getPathOfOnlineDocumentation();
        return $this->extensionConfiguration->isOnlineDocumentationEnabled() && ($prefixedUrlPath === $apiExplorerPrefix || strpos(
            $prefixedUrlPath,
            $apiExplorerPrefix . '/'
        ) === 0);
    }
}
