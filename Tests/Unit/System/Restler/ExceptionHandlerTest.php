<?php
namespace Aoe\Restler\Tests\Unit\System\Restler;

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
     * @test
     */
    public function canHandleAllExceptions($statusCode, $statusName)
    {
        if ($statusName) {
            $message = $statusName;
        } else {
            $message = 'dummy-message';
        }

        $restler = $this->getMockBuilder('Luracast\\Restler\\Restler')->disableOriginalConstructor()->getMock();

        $exceptionHandler = $this->getMockBuilder('Aoe\\Restler\\Tests\\Unit\\System\\Restler\\Fixtures\\ExceptionHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRestler', 'getRestlerException'))
            ->getMock();
        $exceptionHandler->expects($this->any())->method('getRestler')->will($this->returnValue($restler));

        $restlerException = new RestException($statusCode, $message);

        $exceptionHandler->expects($this->any())->method('getRestlerException')->will($this->returnValue($restlerException));

        $functionName = 'handle'.$statusCode;
        $this->assertEquals($message, $exceptionHandler->$functionName());
    }

    public function statusCodes()
    {
        return array(
            array(100, 'Continue'),
            array(101, 'Switching Protocols'),
            array(102, 'Processing'),
            array(200, 'OK'),
            array(201, 'Created'),
            array(202, 'Accepted'),
            array(203, 'Non-Authoritative Information'),
            array(204, 'No Content'),
            array(205, 'Reset Content'),
            array(206, 'Partial Content'),
            array(207, 'Multi-Status'),
            array(208, 'Already Reported'),
            array(226, 'IM Used'),
            array(300, 'Multiple Choices'),
            array(301, 'Moved Permanently'),
            array(302, 'Found'),
            array(303, 'See Other'),
            array(304, 'Not Modified'),
            array(305, 'Use Proxy'),
            array(306, 'Switch Proxy'),
            array(307, 'Temporary Redirect'),
            array(308, 'Permanent Redirect'),
            array(400, 'Bad Request'),
            array(401, 'Unauthorized'),
            array(402, 'Payment Required'),
            array(403, 'Forbidden'),
            array(404, 'Not Found'),
            array(405, 'Method Not Allowed'),
            array(406, 'Not Acceptable'),
            array(407, 'Proxy Authentication Required'),
            array(408, 'Request Timeout'),
            array(409, 'Conflict'),
            array(410, 'Gone'),
            array(411, 'Length Required'),
            array(412, 'Precondition Failed'),
            array(413, 'Request Entity Too Large'),
            array(414, 'Request-URI Too Long'),
            array(415, 'Unsupported Media Type'),
            array(416, 'Requested Range Not Satisfiable'),
            array(417, 'Expectation Failed'),
            array(418, 'I\'m a teapot'),
            array(420, ''),
            array(421, 'Misdirected Request'),
            array(422, 'Unprocessable Entity'),
            array(423, 'Locked'),
            array(424, 'Failed Dependency'),
            array(425, 'Unordered Collection'),
            array(426, 'Upgrade Required'),
            array(428, 'Precondition Required'),
            array(429, 'Too Many Requests'),
            array(430, ''),
            array(431, 'Request Header Fields Too Large'),
            array(444, 'No Response'),
            array(449, 'Retry With'),
            array(451, 'Unavailable For Legal Reasons'),
            array(500, 'Internal Server Error'),
            array(501, 'Not Implemented'),
            array(502, 'Bad Gateway'),
            array(503, 'Service Unavailable'),
            array(504, 'Gateway Timeout'),
            array(505, 'HTTP Version Not Supported'),
            array(506, 'Variant Also Negotiates'),
            array(507, 'Insufficient Storage'),
            array(508, ''),
            array(509, 'Bandwidth Limit Exceeded'),
            array(510, 'Not Extended'),
            array(900, ''),
            array(901, ''),
            array(902, ''),
            array(903, ''),
            array(904, ''),
            array(905, ''),
            array(906, ''),
            array(907, ''),
            array(950, ''),
        );
    }
}
