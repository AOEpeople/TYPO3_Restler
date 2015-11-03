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

use Luracast\Restler\Restler;
use Luracast\Restler\RestException;
use Luracast\Restler\Defaults;
use Exception;

/**
 * This class represents a single REST-API-request, which we can be called from PHP.
 * For each REST-API request, we need a new object of this class, because restler stores some data in this object
 *
 * The logic/idea behind this class is:
 * 1. We override the data of $_GET, $_POST and $_SERVER - because this data 'defines' the REST-API-request, which we want to call
 * 2. We call/start restler to handle the REST-API-request
 * 3. We return the result of the 'called' REST-API-request - instead of sending the result to the client
 */
class RestApiRequest extends Restler
{
    /**
     * store data from $_GET in this property
     *
     * Attention:
     * This property must be static, because it can happen, that some REST-API-calls
     * will be called recursive, so we MUST store the 'realy original' data
     *
     * @var array
     */
    private static $originalGetVars;
    /**
     * store data from $_POST in this property
     *
     * Attention:
     * This property must be static, because it can happen, that some REST-API-calls
     * will be called recursive, so we MUST store the 'realy original' data
     *
     * @var array
     */
    private static $originalPostVars;
    /**
     * store data from $_SERVER in this property
     *
     * Attention:
     * This property must be static, because it can happen, that some REST-API-calls
     * will be called recursive, so we MUST store the 'realy original' data
     *
     * @var array
     */
    private static $originalServerSettings;
    /**
     * @var array
     */
    private $restApiGetData;
    /**
     * @var array
     */
    private $restApiPostData;
    /**
     * This property defines the request-uri (without GET-params, e.g. '/api/products/320'), which should be called
     *
     * @var string
     */
    private $restApiRequestUri;
    /**
     * This property defines the request-method (e.g. 'GET', 'POST', 'PUT' or 'DELETE'), which should be used while calling the rest-api
     *
     * @var string
     */
    private $restApiRequestMethod;



    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /* Block of methods, which does NOT override logic from parent-class *******************************************************/
    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /**
     * @return mixed can be a primitive or array or object
     * @throws RestException
     */
    public function executeRestApiRequest($requestMethod, $requestUri, array $getData = array(), array $postData = array())
    {
        $this->restApiRequestMethod = $requestMethod;
        $this->restApiRequestUri = $requestUri;
        $this->restApiGetData = $getData;
        $this->restApiPostData = $postData;

        $this->storeGlobalData();
        $this->overrideGlobalData();
        try {
            $result = $this->handle();
            $this->resetGlobalData();
            return $result;
        } catch(RestException $e) {
            $this->resetGlobalData();
            throw $e;
        } catch(Exception $e) {
            $this->resetGlobalData();
            throw new RestException(400, $e->getMessage());
        }
    }

    /**
     * Override (the stored) data of $_GET, $_POST and $_SERVER (which are used in several restler-PHP-classes), because
     * this data 'defines' the REST-API-request, which we want to call
     *
     * @return void
     */
    private function overrideGlobalData()
    {
        $_GET = $this->restApiGetData;
        $_POST = $this->restApiPostData;
        $_SERVER['REQUEST_METHOD'] = $this->restApiRequestMethod;
        $_SERVER['REQUEST_URI'] = $this->restApiRequestUri;
    }

    /**
     * Reset (the overridden) data of $_GET, $_POST and $_SERVER
     * @return void
     */
    private function resetGlobalData()
    {
        $_GET = self::$originalGetVars;
        $_POST = self::$originalPostVars;
        $_SERVER = self::$originalServerSettings;
    }

    /**
     * Store (the original) data of $_GET, $_POST and $_SERVER
     * @return void
     */
    private function storeGlobalData()
    {
        if (false === isset(self::$originalServerSettings)) {
            self::$originalGetVars = $_GET;
            self::$originalPostVars = $_POST;
            self::$originalServerSettings = $_SERVER;
        }
    }



    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /* Block of methods, which MUST be overriden from parent-class (otherwise this class can not work) *************************/
    /***************************************************************************************************************************/
    /***************************************************************************************************************************/
    /**
     * Override parent method...because we don't want to call it!
     * The original method would set some properties (e.g. set this object into static properties of global classes)
     *
     * @param boolean $productionMode    When set to false, it will run in debug mode and parse the class files every time to map it to the URL
     * @param boolean $refreshCache      will update the cache when set to true
     */
    public function __construct($productionMode = false, $refreshCache = false)
    {
    }

    /**
     * Override parent method...because we don't want to call it (the original method would send some headers to the client)!
     */
    public function composeHeaders(RestException $e = null)
    {
    }

    /**
     * Override parent method...because we don't want to call it (the original method would cache the determined routes)!
     */
    public function __destruct()
    {
    }

    /**
     * Override parent method...because we must return the data of the REST-API request and we need NO exception-handling!
     *
     * @return mixed can be a primitive or array or object
     */
    public function handle()
    {
        $this->get();
        if (Defaults::$useVendorMIMEVersioning) {
            $this->responseFormat = $this->negotiateResponseFormat();
        }
        $this->route();
        $this->negotiate();
        $this->preAuthFilter();
        $this->authenticate();
        $this->postAuthFilter();
        $this->validate();
        $this->preCall();
        $this->call();
        $this->compose();
        $this->postCall();

        return $this->responseFormat->decode($this->responseData);
    }

    /**
     * Override parent method...because we must return the request-data of THIS REST-API request!
     * The original method would return the request-data of the ORIGINAL called REST-API request
     *
     * @param boolean $includeQueryParameters
     *
     * @return array
     */
    public function getRequestData($includeQueryParameters = true)
    {
        $requestData = array();
        if ($this->restApiRequestMethod == 'PUT' || $this->restApiRequestMethod == 'PATCH' || $this->restApiRequestMethod == 'POST') {
            $requestData = array_merge($this->restApiPostData, array(Defaults::$fullRequestDataName => $this->restApiPostData));
        }

        if ($includeQueryParameters === true) {
            return $requestData + $this->restApiGetData;
        }
        return $requestData;
    }
}
