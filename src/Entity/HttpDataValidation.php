<?php
/**
 * Author: jairo.rodriguez <jairo@bfunky.net>
 */

namespace BFunky\HttpParser\Entity;

use BFunky\HttpParser\Exception\HttpParserBadFormatException;

class HttpDataValidation
{
    public static function isField(string $httpLine): bool
    {
        $separatorPosition = strpos($httpLine, ':');

        if ($separatorPosition === false || $separatorPosition === 0) {
            return false;
        }

        return preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", trim(substr($httpLine, 0, $separatorPosition))) === 1;
    }

    /**
     * @throws HttpParserBadFormatException
     */
    public static function checkHeaderOrRaiseError(string $method, string $path, string $protocol): void
    {
        if (empty($method) || empty($path) || empty($protocol)) {
            throw new HttpParserBadFormatException();
        }
    }
}
