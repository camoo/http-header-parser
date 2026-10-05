<?php
/**
 * Author: Jairo Rodríguez <jairo@bfunky.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFunky\Test\HttpParser\Entity;

use BFunky\HttpParser\Entity\HttpDataValidation;
use BFunky\HttpParser\Exception\HttpParserBadFormatException;
use PHPUnit\Framework\TestCase;

class HttpDataValidationTest extends TestCase
{
    public function testIsFieldValidationWithAFieldLine()
    {
        $line = 'Pragma: no-cache';
        $this->assertTrue(HttpDataValidation::isField($line));
    }

    public function testIsFieldValidationWithAHeader()
    {
        $line = 'HTTP/1.1 200 OK';
        $this->assertFalse(HttpDataValidation::isField($line));
    }

    public function testCheckHeaderOrRaiseErrorWithAWrongHeader()
    {
        $this->expectException(HttpParserBadFormatException::class);
        HttpDataValidation::checkHeaderOrRaiseError('POST', '', '');
    }

    public function testCheckHeaderOrRaiseErrorWithACorrectHeader()
    {
        $return = HttpDataValidation::checkHeaderOrRaiseError('POST', '/path', 'HTTP/1.1');
        $this->assertNull($return);
    }

    public function testCheckResponseHeaderOrRaiseErrorWithValidHeaders()
    {
        $this->assertNull(HttpDataValidation::checkResponseHeaderOrRaiseError('HTTP/1.1', '200'));
        $this->assertNull(HttpDataValidation::checkResponseHeaderOrRaiseError('HTTP/2', '204'));
    }

    public function testCheckResponseHeaderOrRaiseErrorWithInvalidProtocol()
    {
        $this->expectException(HttpParserBadFormatException::class);
        HttpDataValidation::checkResponseHeaderOrRaiseError('POST', '200');
    }

    public function testCheckResponseHeaderOrRaiseErrorWithNonNumericCode()
    {
        $this->expectException(HttpParserBadFormatException::class);
        HttpDataValidation::checkResponseHeaderOrRaiseError('HTTP/1.1', 'OK');
    }
}
