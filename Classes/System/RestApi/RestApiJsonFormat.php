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

use Luracast\Restler\Format\JsonFormat;
use Luracast\Restler\RestException;
use TYPO3\CMS\Core\SingletonInterface;

class RestApiJsonFormat extends JsonFormat implements SingletonInterface
{
    /**
     * @param string $data
     */
    public function decode($data)
    {
        $options = 0;
        if (self::$bigIntAsString) {
            if ((PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 4) // PHP >= 5.4
                || PHP_MAJOR_VERSION > 5 // PHP >= 6.0
            ) {
                $options |= JSON_BIGINT_AS_STRING;
            } else {
                $data = preg_replace(
                    '/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/',
                    ': "$1"',
                    $data
                );
            }
        }

        try {
            $decoded = json_decode($data, false, 512, $options);
            $this->handleJsonError();
        } catch (\RuntimeException $runtimeException) {
            throw new RestException('400', $runtimeException->getMessage());
        }

        if (strlen($data) && $decoded === null || $decoded === $data) {
            throw new RestException('400', 'Error parsing JSON');
        }

        return $decoded;
    }
}
