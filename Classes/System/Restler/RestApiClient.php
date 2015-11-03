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
use Luracast\Restler\Scope;
use Luracast\Restler\RestException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @package Restler
 */
class RestApiClient implements SingletonInterface
{
    /**
     * @var Restler
     */
    private $restler;

    /**
     * set private property $restler
     */
    public function __construct()
    {
        $this->restler = Scope::get('Restler');
    }

    /**
     * @param string $requestMethod e.g. 'GET', 'POST', 'PUT' or 'DELETE'
     * @param string $requestUri   e.g. '/api/products/320' (without GET-params)
     * @param array $getData
     * @param array $postData
     * @return mixed can be a primitive or array or object
     * @throws RestApiRequestException
     */
    public function executeRequest($requestMethod, $requestUri, array $getData = array(), array $postData = array())
    {
        try {
            $restApiRequest = new RestApiRequest($this->restler->getProductionMode(), $this->restler->refreshCache);
            return $restApiRequest->executeRestApiRequest($requestMethod, $requestUri, $getData, $postData);
        } catch(RestException $e) {
            $errorMessage = 'internal REST-API-request \''.$requestMethod.':'.$requestUri.'\' could not be processed';
            if (false === $this->restler->getProductionMode()) {
                $errorMessage .= ' (message: '.$e->getMessage().', details: '.json_encode($e->getDetails()).')';
            }
            throw new RestApiRequestException(
                $errorMessage,
                RestApiRequestException::EXCEPTION_CODE_REQUEST_COULD_NOT_PROCESSED,
                $e
            );
        }
    }
}