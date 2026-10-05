<?php
/**
 * Author: jairo.rodriguez <jairo@bfunky.net>
 */

namespace BFunky\HttpParser\Entity;

use BFunky\HttpParser\Exception\HttpFieldNotFoundOnCollection;

class HttpFieldCollection
{
    /**
     * HttpFieldCollection constructor.
     *
     * @param HttpField[] $httpFields
     */
    public function __construct(private array $httpFields = [])
    {
        $fields = $this->httpFields;
        $this->httpFields = [];

        foreach ($fields as $httpField) {
            if (is_array($httpField)) {
                foreach ($httpField as $field) {
                    $this->add($field);
                }

                continue;
            }

            $this->add($httpField);
        }
    }

    public function add(HttpField $obj): void
    {
        $key = $this->normalizeKey($obj->getName());

        if (array_key_exists($key, $this->httpFields)) {
            if (!is_array($this->httpFields[$key])) {
                $firstValue = $this->httpFields[$key];
                $this->httpFields[$key] = [];
                $this->httpFields[$key][] = $firstValue;
            }
            $this->httpFields[$key][] = $obj;

            return;
        }
        $this->httpFields[$key] = $obj;
    }

    /** @return array<HttpField> */
    public function getHttpFields(): array
    {
        return $this->httpFields;
    }

    /** @throws HttpFieldNotFoundOnCollection */
    public function delete(string $key): void
    {
        $this->checkKeyExists($key);
        unset($this->httpFields[$this->normalizeKey($key)]);
    }

    /** @throws HttpFieldNotFoundOnCollection */
    public function get(string $key): HttpField|array
    {
        $this->checkKeyExists($key);

        return $this->httpFields[$this->normalizeKey($key)];
    }

    public static function fromHttpFieldArray(array $httpFields): self
    {
        return new self($httpFields);
    }

    /** @throws HttpFieldNotFoundOnCollection */
    private function checkKeyExists(string $key): void
    {
        if (!array_key_exists($this->normalizeKey($key), $this->httpFields)) {
            throw new  HttpFieldNotFoundOnCollection('Field ' . $key . ' not found');
        }
    }

    private function normalizeKey(string $key): string
    {
        return strtolower($key);
    }
}
