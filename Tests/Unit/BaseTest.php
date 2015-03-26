<?php
namespace Aoe\Restler\Tests\Unit;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
 * @package Checkout
 * @subpackage Tests
 *
 */
abstract class BaseTest extends UnitTestCase
{
    /**
     * @var ObjectManagerInterface The object manager
     */
    protected $objectManager;

    /**
     * call unaccessible method of an object (to test it)
     * @param  unknown_type $object
     * @param  unknown_type $methodName
     * @param  array $methodParams
     * @return mixed
     */
    protected function callUnaccessibleMethodOfObject($object, $methodName, array $methodParams = array())
    {
        $class = new \ReflectionClass(get_class($object));
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $methodParams);
    }

    /**
     * Initialization.
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->objectManager = clone $objectManager;
    }
}
