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

use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

/**
 * This abstract class can be used to call ONE central method,
 * which handles ALL exceptions (the current HTTP-Status-Code doesn't matter)!
 */
abstract class AbstractExceptionHandler
{
    /**
     * handle HTTP-Status-Codes of type 1xx
     */
    public function handle100(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle101(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle102(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 2xx
     */
    public function handle200(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle201(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle202(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle203(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle204(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle205(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle206(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle207(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle208(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle226(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 3xx
     */
    public function handle300(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle301(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle302(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle303(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle304(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle305(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle306(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle307(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle308(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 4xx
     */
    public function handle400(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle401(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle402(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle403(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle404(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle405(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle406(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle407(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle408(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle409(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle410(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle411(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle412(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle413(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle414(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle415(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle416(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle417(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle418(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle420(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle421(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle422(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle423(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle424(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle425(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle426(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle428(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle429(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle430(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle431(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle444(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle449(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle451(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 5xx
     */
    public function handle500(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle501(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle502(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle503(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle504(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle505(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle506(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle507(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle508(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle509(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle510(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 9xx
     */
    public function handle900(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle901(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle902(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle903(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle904(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle905(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle906(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle907(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    public function handle950(): bool
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * This is the right place, where we can handle exceptions, which were thrown in REST-API's!
     *
     * The return value (boolean) describes, if restler should display an error as output:
     * TRUE means:  restler should NOT display an error as output (so, we must do that)
     * FALSE means: restler should display an error as output
     */
    abstract protected function handleException(RestException $exception, Restler $restler): bool;

    /**
     * This method must be protected - otherwise we can't test this class in unittests
     */
    protected function getRestler(): Restler
    {
        return Scope::get('Restler');
    }

    /**
     * This method must be protected - otherwise we can't test this class in unittests
     */
    protected function getRestlerException(array $exceptionHandlerArgs = []): RestException
    {
        if ($exceptionHandlerArgs !== [] && $exceptionHandlerArgs[0] instanceof RestException) {
            return $exceptionHandlerArgs[0];
        }

        return $this->getRestler()
            ->exception;
    }
}
