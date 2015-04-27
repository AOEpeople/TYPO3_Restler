<?php
namespace Aoe\Restler\System\Restler;

use Aoe\Restler\Configuration\ExtensionConfiguration;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

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
 * This abstract class can be used to call ONE central method, which handles ALL exceptions (the current HTTP-Status-Code doesn't matter)!
 *
 * @package Restler
 *
 * @codeCoverageIgnore
 */
abstract class AbstractExceptionHandler
{
    /**
     * This object can be used, to check the Extension-Configuration (maybe we want to
     * handle an exception in different ways - based on the Extension-Configuration)
     *
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }


    /**
     * handle HTTP-Status-Codes of type 1xx
     */
    public function handle100()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle101()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle102()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 2xx
     */
    public function handle200()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle201()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle202()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle203()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle204()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle205()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle206()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle207()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle208()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle226()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 3xx
     */
    public function handle300()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle301()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle302()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle303()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle304()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle305()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle306()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle307()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle308()
    {
        $this->handleException($this->getRestlerException(), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 4xx
     */
    public function handle400()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle401()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle402()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle403()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle404()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle405()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle406()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle407()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle408()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle409()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle410()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle411()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle412()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle413()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle414()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle415()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle416()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle417()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle418()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle420()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle421()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle422()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle423()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle424()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle425()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle426()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle428()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle429()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle430()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle431()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle444()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle449()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle451()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 5xx
     */
    public function handle500()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle501()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle502()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle503()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle504()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle505()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle506()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle507()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle508()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle509()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle510()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 9xx
     */
    public function handle900()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle901()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle902()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle903()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle904()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle905()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle906()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle907()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }
    public function handle950()
    {
        return $this->handleException($this->getRestlerException(), $this->getRestler());
    }

    /**
     * This is the right place, where we can handle exceptions, which were thrown in REST-API's!
     *
     * The return value (boolean) describes, if restler should display an error as output:
     * TRUE means:  restler should NOT display an error as output (so, we must do that)
     * FALSE means: restler should dislay an error as output
     *
     * @param RestException $exception
     * @param Restler $restler
     * @return boolean
     */
    abstract protected function handleException(RestException $exception, Restler $restler);

    /**
     * This method must be protected - otherwise we can't test this class in unittests
     *
     * @return Restler
     */
    protected function getRestler()
    {
        return Scope::get('Restler');
    }

    /**
     * This method must be protected - otherwise we can't test this class in unittests
     *
     * @return RestException
     */
    protected function getRestlerException()
    {
        $restler = $this->getRestler();
        return $restler->exception;
    }
}
