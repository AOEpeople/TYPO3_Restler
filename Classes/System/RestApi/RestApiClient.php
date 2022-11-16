<?php

namespace Aoe\Restler\System\RestApi;

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
use Aoe\Restler\System\TYPO3\Cache;
use Luracast\Restler\RestException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\ServerRequest;
use stdClass;

/**
 * @package Restler
 */
class RestApiClient implements SingletonInterface
{
    private Cache $typo3Cache;

    private ExtensionConfiguration $extensionConfiguration;

    private bool $isExecutingRequest = false;

    private bool $isRequestPrepared = false;

    private RestApiRequestScope $restApiRequestScope;

    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        RestApiRequestScope $restApiRequestScope,
        Cache $typo3Cache
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->restApiRequestScope = $restApiRequestScope;
        $this->typo3Cache = $typo3Cache;
    }

    public function isExecutingRequest(): bool
    {
        return $this->isExecutingRequest;
    }

    public function isProductionContextSet(): bool
    {
        return $this->extensionConfiguration->isProductionContextSet();
    }

    /**
     * @param array|stdClass $getData
     * @param array|stdClass $postData
     * @return mixed can be a primitive or array or object
     * @throws RestApiRequestException
     */
    public function executeRequest(string $requestMethod, string $requestUri, $getData = null, $postData = null)
    {
        if ($this->isRequestPreparationRequired()) {
            $this->prepareRequest($requestMethod, $requestUri);
        }

        try {
            $this->isExecutingRequest = true;
            $result = $this->createRequest()
                ->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
            $this->isExecutingRequest = false;
            return $result;
        } catch (RestException $restException) {
            $this->isExecutingRequest = false;
            $restException = $this->createRequestException($restException, $requestMethod, $requestUri);
            throw $restException;
        }
    }

    /**
     * We must create for every REST-API-request a new object, because the object will contain data, which is related to the request
     */
    protected function createRequest(): RestApiRequest
    {
        return new RestApiRequest($this->restApiRequestScope, $this->typo3Cache);
    }

    protected function createRequestException(RestException $e, string $requestMethod, string $requestUri): RestApiRequestException
    {
        $errorMessage = "internal REST-API-request '" . $requestMethod . ':' . $requestUri . "' could not be processed";
        if (!$this->isProductionContextSet()) {
            $errorMessage .= ' (message: ' . $e->getMessage() . ', details: ' . json_encode($e->getDetails(), JSON_THROW_ON_ERROR) . ')';
        }
        return new RestApiRequestException(
            $errorMessage,
            RestApiRequestException::EXCEPTION_CODE_REQUEST_COULD_NOT_PROCESSED,
            $e
        );
    }

    protected function getRestlerBuilder(): RestlerBuilder
    {
        return GeneralUtility::makeInstance(RestlerBuilder::class);
    }

    /**
     * We must prepare the REST-API-request when we are in the 'normal' TYPO3-context (the client, which called this PHP-request, has
     * NOT requested an REST-API-endpoint). In this case, we must build the 'original' REST-API-Request (aka Restler-object, which is
     * always required), before we can execute any REST-API-request via this PHP-client.
     */
    protected function isRequestPreparationRequired(): bool
    {
        return !defined('REST_API_IS_RUNNING') && !$this->isRequestPrepared;
    }

    /**
     * build the 'original' REST-API-Request (aka Restler-object, which is always
     * required) and store it in the REST-API-Request-Scope (aka Scope-object)
     */
    private function prepareRequest(string $requestMethod, string $requestUri): void
    {
        // TODO: pass along the post data
        $originalRestApiRequest = $this->getRestlerBuilder()
            ->build(new ServerRequest($requestUri, $requestMethod));
        $this->restApiRequestScope->storeOriginalRestApiRequest($originalRestApiRequest);
        $this->isRequestPrepared = true;
    }
}
