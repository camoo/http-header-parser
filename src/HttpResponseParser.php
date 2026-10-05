<?php
/**
 * Author: Jairo Rodríguez <jairo@bfunky.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFunky\HttpParser;

use BFunky\HttpParser\Entity\HttpDataValidation;
use BFunky\HttpParser\Entity\HttpResponseHeader;
use BFunky\HttpParser\Exception\HttpParserBadFormatException;

class HttpResponseParser extends AbstractHttpParser
{
    /** @throws HttpParserBadFormatException */
    protected function addHeader(string $headerLine): void
    {
        $data = preg_split('/[ \t]+/', trim($headerLine), 3) ?: [];
        $data = array_pad($data, 3, '');
        HttpDataValidation::checkResponseHeaderOrRaiseError($data[0], $data[1]);
        $this->setHttpHeader($data[0], $data[1], $data[2]);
    }

    /** @inheritdoc */
    protected function setHttpHeader(string $method, string $path, string $protocol): void
    {
        $this->httpHeader = new HttpResponseHeader($method, $path, $protocol);
    }
}
