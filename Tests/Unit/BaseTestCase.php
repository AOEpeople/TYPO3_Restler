<?php

namespace Aoe\Restler\Tests\Unit;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 AOE GmbH <dev@aoe.com>
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

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @package Restler
 * @subpackage Tests
 */
abstract class BaseTestCase extends UnitTestCase
{
    /**
     * call unaccessible method of an object (to test it)
     * @param  object $object
     * @param  string $methodName
     * @return mixed
     */
    protected function callUnaccessibleMethodOfObject($object, $methodName, array $methodParams = [])
    {
        $class = new \ReflectionClass(get_class($object));
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $methodParams);
    }
}
