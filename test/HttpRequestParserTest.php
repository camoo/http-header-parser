<?php
/**
 * Author: Jairo Rodríguez <jairo@bfunky.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFunky\Test\HttpParser;

use BFunky\HttpParser\Entity\HttpRequestHeader;
use BFunky\HttpParser\Exception\HttpParserBadFormatException;
use BFunky\HttpParser\HttpRequestParser;
use PHPUnit\Framework\TestCase;

class HttpRequestParserTest extends TestCase
{
    public function testParseHttpRequestHeader()
    {
        $parser = new HttpRequestParser();
        $raw = <<<RAW
POST /path HTTP/1.1
User-Agent: PHP-SOAP/\BeSimple\SoapClient
Host: url.com:80
Accept: */*
Accept-Encoding: deflate, gzip
Content-Type:text/xml; charset=utf-8
Content-Length: 1108
Expect: 100-continue

RAW;
        $parser->parse($raw);
        $this->assertEquals($parser->get('User-Agent'), 'PHP-SOAP/\BeSimple\SoapClient');
        $this->assertEquals($parser->get('Host'), 'url.com:80');
        $this->assertEquals($parser->get('Accept'), '*/*');
        $this->assertEquals($parser->get('Accept-Encoding'), 'deflate, gzip');
        $this->assertEquals($parser->get('Content-Type'), 'text/xml; charset=utf-8');
        $this->assertEquals($parser->get('Content-Length'), '1108');
        $this->assertEquals($parser->get('Expect'), '100-continue');
        /**
         * @var HttpRequestHeader $entityHeader
         */
        $entityHeader = $parser->getHeader();
        $this->assertInstanceOf('\BFunky\HttpParser\Entity\HttpRequestHeader', $entityHeader);
        $this->assertEquals($entityHeader->getMethod(), 'POST');
        $this->assertEquals($entityHeader->getPath(), '/path');
        $this->assertEquals($entityHeader->getProtocol(), 'HTTP/1.1');
    }

    public function testThrownExceptionIfWrongRawData()
    {
        $this->expectException(HttpParserBadFormatException::class);
        $parser = new HttpRequestParser();
        $raw = <<<RAW
POST /path
User-Agent: PHP-SOAP/\BeSimple\SoapClient

RAW;
        $parser->parse($raw);
    }

    public function testParsesCrLfHeadersAndValuesContainingColons(): void
    {
        $parser = new HttpRequestParser();
        $parser->parse("GET http://example.test/path HTTP/1.1\r\nLocation:http://example.test/next?a=1:2\r\n\r\n");

        $this->assertSame('http://example.test/next?a=1:2', $parser->get('location'));
        $this->assertSame('GET', $parser->getHeader()->getMethod());
        $this->assertSame('http://example.test/path', $parser->getHeader()->getPath());
    }

    public function testParsingAgainDoesNotRetainFieldsFromPreviousMessage(): void
    {
        $parser = new HttpRequestParser();
        $parser->parse("GET /first HTTP/1.1\nX-First: one\n\n");
        $parser->parse("GET /second HTTP/1.1\nX-Second: two\n\n");

        $this->assertSame('/second', $parser->getHeader()->getPath());
        $this->expectException(\BFunky\HttpParser\Exception\HttpFieldNotFoundOnCollection::class);
        $parser->get('X-First');
    }

    public function testEmptyInputIsRejected(): void
    {
        $this->expectException(HttpParserBadFormatException::class);

        (new HttpRequestParser())->parse("\r\n\r\n");
    }
}
