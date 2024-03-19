<?php

namespace Aoe\Restler\Tests\Unit\System\Restler;

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

use Aoe\Restler\Tests\Unit\BaseTest;
use Aoe\Restler\Tests\Unit\System\Restler\Fixtures\ExceptionHandler;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;

/**
 * @package Restler
 * @subpackage Tests
 *
 * @covers \Aoe\Restler\System\Restler\AbstractExceptionHandler
 */
class ExceptionHandlerTest extends BaseTest
{
    /**
     * @dataProvider statusCodes
     */
    public function testCanHandleAllExceptions(int $statusCode, string $statusName): void
    {
        $message = $statusName !== '' && $statusName !== '0' ? $statusName : 'dummy-message';

        $restler = $this->getMockBuilder(Restler::class)->disableOriginalConstructor()->getMock();

        $exceptionHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRestler', 'getRestlerException'])
            ->getMock();
        $exceptionHandler->expects(self::any())->method('getRestler')->willReturn($restler);

        $restlerException = new RestException($statusCode, $message);

        $exceptionHandler->expects(self::any())->method('getRestlerException')->willReturn($restlerException);

        $functionName = 'handle' . $statusCode;
        $this->assertEquals($message, $exceptionHandler->$functionName());
    }

    public static function statusCodes(): array
    {
        return [
            [100, 'Continue'],
            [101, 'Switching Protocols'],
            [102, 'Processing'],
            [200, 'OK'],
            [201, 'Created'],
            [202, 'Accepted'],
            [203, 'Non-Authoritative Information'],
            [204, 'No Content'],
            [205, 'Reset Content'],
            [206, 'Partial Content'],
            [207, 'Multi-Status'],
            [208, 'Already Reported'],
            [226, 'IM Used'],
            [300, 'Multiple Choices'],
            [301, 'Moved Permanently'],
            [302, 'Found'],
            [303, 'See Other'],
            [304, 'Not Modified'],
            [305, 'Use Proxy'],
            [306, 'Switch Proxy'],
            [307, 'Temporary Redirect'],
            [308, 'Permanent Redirect'],
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [402, 'Payment Required'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [405, 'Method Not Allowed'],
            [406, 'Not Acceptable'],
            [407, 'Proxy Authentication Required'],
            [408, 'Request Timeout'],
            [409, 'Conflict'],
            [410, 'Gone'],
            [411, 'Length Required'],
            [412, 'Precondition Failed'],
            [413, 'Request Entity Too Large'],
            [414, 'Request-URI Too Long'],
            [415, 'Unsupported Media Type'],
            [416, 'Requested Range Not Satisfiable'],
            [417, 'Expectation Failed'],
            [418, "I'm a teapot"],
            [420, ''],
            [421, 'Misdirected Request'],
            [422, 'Unprocessable Entity'],
            [423, 'Locked'],
            [424, 'Failed Dependency'],
            [425, 'Unordered Collection'],
            [426, 'Upgrade Required'],
            [428, 'Precondition Required'],
            [429, 'Too Many Requests'],
            [430, ''],
            [431, 'Request Header Fields Too Large'],
            [444, 'No Response'],
            [449, 'Retry With'],
            [451, 'Unavailable For Legal Reasons'],
            [500, 'Internal Server Error'],
            [501, 'Not Implemented'],
            [502, 'Bad Gateway'],
            [503, 'Service Unavailable'],
            [504, 'Gateway Timeout'],
            [505, 'HTTP Version Not Supported'],
            [506, 'Variant Also Negotiates'],
            [507, 'Insufficient Storage'],
            [508, ''],
            [509, 'Bandwidth Limit Exceeded'],
            [510, 'Not Extended'],
            [900, ''],
            [901, ''],
            [902, ''],
            [903, ''],
            [904, ''],
            [905, ''],
            [906, ''],
            [907, ''],
            [950, ''],
        ];
    }
}
