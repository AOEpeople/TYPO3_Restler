<?php

declare(strict_types=1);

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
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

/**
 * This abstract class can be used to call ONE central method,
 * which handles ALL exceptions (the current HTTP-Status-Code doesn't matter)!
 */
abstract class AbstractExceptionHandler
{
    protected ExtensionConfiguration $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }


    /**
     * handle HTTP-Status-Codes of type 1xx
     */
    public function handle100(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle101(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle102(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 2xx
     */
    public function handle200(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle201(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle202(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle203(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle204(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle205(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle206(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle207(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle208(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle226(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 3xx
     */
    public function handle300(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle301(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle302(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle303(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle304(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle305(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle306(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle307(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle308(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 4xx
     */
    public function handle400(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle401(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle402(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle403(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle404(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle405(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle406(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle407(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle408(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle409(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle410(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle411(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle412(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle413(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle414(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle415(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle416(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle417(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle418(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle420(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle421(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle422(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle423(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle424(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle425(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle426(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle428(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle429(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle430(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle431(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle444(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle449(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle451(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    /**
     * handle HTTP-Status-Codes of type 5xx
     */
    public function handle500(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle501(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle502(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle503(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle504(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle505(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle506(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle507(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle508(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle509(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle510(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }


    /**
     * handle HTTP-Status-Codes of type 9xx
     */
    public function handle900(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle901(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle902(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle903(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle904(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle905(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle906(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle907(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }
    public function handle950(): string
    {
        return $this->handleException($this->getRestlerException(func_get_args()), $this->getRestler());
    }

    abstract protected function handleException(RestException $exception, Restler $restler): string;

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
