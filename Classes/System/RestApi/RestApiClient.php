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
use Aoe\Restler\System\TYPO3\Cache as Typo3Cache;
use Luracast\Restler\RestException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use stdClass;

/**
 * @package Restler
 */
class RestApiClient implements SingletonInterface
{
    /**
     * @var Typo3Cache
     */
    private $typo3Cache;
    /**
     * @var ExtensionConfiguration
     */
    private $extensionConfiguration;
    /**
     * @var boolean
     */
    private $isExecutingRequest = false;
    /**
     * @var boolean
     */
    private $isRequestPrepared = false;
    /**
     * @var RestApiRequestScope
     */
    private $restApiRequestScope;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     * @param RestApiRequestScope $restApiRequestScope
     * @param Typo3Cache $typo3Cache
     */
    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        RestApiRequestScope $restApiRequestScope,
        Typo3Cache $typo3Cache
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->restApiRequestScope = $restApiRequestScope;
        $this->typo3Cache = $typo3Cache;
    }

    /**
     * @return boolean
     */
    public function isExecutingRequest()
    {
        return $this->isExecutingRequest;
    }

    /**
     * @return boolean
     */
    public function isProductionContextSet()
    {
        return $this->extensionConfiguration->isProductionContextSet();
    }

    /**
     * @param string $requestMethod e.g. 'GET', 'POST', 'PUT' or 'DELETE'
     * @param string $requestUri   e.g. '/api/products/320' (without GET-params)
     * @param array|stdClass $getData
     * @param array|stdClass $postData
     * @return mixed can be a primitive or array or object
     * @throws RestApiRequestException
     */
    public function executeRequest($requestMethod, $requestUri, $getData = null, $postData = null)
    {
        if ($this->isRequestPreparationRequired()) {
            $this->prepareRequest();
        }

        try {
            $this->isExecutingRequest = true;
            $result = $this->createRequest()->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
            $this->isExecutingRequest = false;
            return $result;
        } catch (RestException $e) {
            $this->isExecutingRequest = false;
            $e = $this->createRequestException($e, $requestMethod, $requestUri);
            throw $e;
        }
    }

    /**
     * We must create for every REST-API-request a new object, because the object will contain data, which is related to the request
     *
     * @return RestApiRequest
     */
    protected function createRequest()
    {
        return new RestApiRequest($this->restApiRequestScope, $this->typo3Cache);
    }

    /**
     * @param RestException $e
     * @param string        $requestMethod
     * @param string        $requestUri
     * @return RestApiRequestException
     */
    protected function createRequestException(RestException $e, $requestMethod, $requestUri)
    {
        $errorMessage = 'internal REST-API-request \''.$requestMethod.':'.$requestUri.'\' could not be processed';
        if (false === $this->isProductionContextSet()) {
            $errorMessage .= ' (message: '.$e->getMessage().', details: '.json_encode($e->getDetails()).')';
        }
        return new RestApiRequestException(
            $errorMessage,
            RestApiRequestException::EXCEPTION_CODE_REQUEST_COULD_NOT_PROCESSED,
            $e
        );
    }

    /**
     * @return RestlerBuilder
     */
    protected function getRestlerBuilder()
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        return $objectManager->get('Aoe\\Restler\\System\\Restler\\Builder');
    }

    /**
     * We must prepare the REST-API-request when we are in the 'normal' TYPO3-context (the client, which called this PHP-request, has
     * NOT requested an REST-API-endpoint). In this case, we must build the 'original' REST-API-Request (aka Restler-object, which is
     * always required), before we can execute any REST-API-request via this PHP-client.
     *
     * @return boolean
     */
    protected function isRequestPreparationRequired()
    {
        if (defined('REST_API_IS_RUNNING') || $this->isRequestPrepared === true) {
            return false;
        }
        return true;
    }

    /**
     * build the 'original' REST-API-Request (aka Restler-object, which is always
     * required) and store it in the REST-API-Request-Scope (aka Scope-object)
     */
    private function prepareRequest()
    {
        $originalRestApiRequest = $this->getRestlerBuilder()->build();
        $this->restApiRequestScope->storeOriginalRestApiRequest($originalRestApiRequest);
        $this->isRequestPrepared = true;
    }
}
