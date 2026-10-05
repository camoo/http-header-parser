<?php
/**
 * Author: Jairo Rodríguez <jairo@bfunky.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFunky\Test\HttpParser;

use BFunky\HttpParser\Entity\HttpResponseHeader;
use BFunky\HttpParser\Exception\HttpParserBadFormatException;
use BFunky\HttpParser\HttpResponseParser;
use PHPUnit\Framework\TestCase;

class HttpResponseParserTest extends TestCase
{
    public function testParseHttpResponseHeader()
    {
        $parser = new HttpResponseParser();
        $raw = <<<RAW
HTTP/1.1 100 Continue

HTTP/1.1 200 OK
Date: Tue, 12 Apr 2016 13:58:01 GMT
Server: Apache/2.2.14 (Ubuntu)
X-Powered-By: PHP/5.3.14 ZendServer/5.0
Set-Cookie: ZDEDebuggerPresent=php,phtml,php3; path=/
Set-Cookie: PHPSESSID=6sf8fa8rlm8c44avk33hhcegt0; path=/; HttpOnly
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Pragma: no-cache
Vary: Accept-Encoding
Content-Encoding: gzip
Content-Length: 192
Content-Type: text/xml
RAW;
        $parser->parse($raw);
        $this->assertEquals($parser->get('Date'), 'Tue, 12 Apr 2016 13:58:01 GMT');
        $this->assertEquals($parser->get('Server'), 'Apache/2.2.14 (Ubuntu)');
        $this->assertEquals($parser->get('X-Powered-By'), 'PHP/5.3.14 ZendServer/5.0');
        $this->assertEquals($parser->get('Set-Cookie'), "ZDEDebuggerPresent=php,phtml,php3; path=/\nPHPSESSID=6sf8fa8rlm8c44avk33hhcegt0; path=/; HttpOnly");
        $this->assertEquals($parser->get('Expires'), 'Thu, 19 Nov 1981 08:52:00 GMT');
        $this->assertEquals($parser->get('Cache-Control'), 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->assertEquals($parser->get('Pragma'), 'no-cache');
        $this->assertEquals($parser->get('Vary'), 'Accept-Encoding');
        $this->assertEquals($parser->get('Content-Encoding'), 'gzip');
        $this->assertEquals($parser->get('Content-Length'), '192');
        $this->assertEquals($parser->get('Content-Type'), 'text/xml');
        /**
         * @var HttpResponseHeader $entityHeader
         */
        $entityHeader = $parser->getHeader();
        $this->assertInstanceOf('\BFunky\HttpParser\Entity\HttpResponseHeader', $entityHeader);
        $this->assertEquals($entityHeader->getProtocol(), 'HTTP/1.1');
        $this->assertEquals($entityHeader->getCode(), '200');
        $this->assertEquals($entityHeader->getMessage(), 'OK');
    }

    public function testThrownExceptionIfWrongRawData()
    {
        $this->expectException(HttpParserBadFormatException::class);
        $parser = new HttpResponseParser();
        $raw = <<<RAW
POST /path
User-Agent: PHP-SOAP/\BeSimple\SoapClient

RAW;
        $parser->parse($raw);
    }

    public function testPreservesTheCompleteReasonPhrase(): void
    {
        $parser = new HttpResponseParser();
        $parser->parse("HTTP/1.1 404 Not Found\r\nContent-Type: text/plain\r\n\r\n");

        $this->assertSame('404', $parser->getHeader()->getCode());
        $this->assertSame('Not Found', $parser->getHeader()->getMessage());
    }

    public function testExposesTheParsedFieldCollection(): void
    {
        $parser = new HttpResponseParser();
        $parser->parse("HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nX-Test: one\r\n\r\n");

        $fields = $parser->getHttpFieldCollection()->getHttpFields();

        $this->assertArrayHasKey('content-type', $fields);
        $this->assertArrayHasKey('x-test', $fields);
        $this->assertSame('Content-Type', $fields['content-type']->getName());
        $this->assertSame('application/json', $fields['content-type']->getValue());
    }
}
