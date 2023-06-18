<?php
/**
 * Author: jairo.rodriguez <jairo@bfunky.net>
 */

namespace BFunky\HttpParser\Entity;

use BFunky\HttpParser\Exception\HttpFieldNotFoundOnCollection;

class HttpFieldCollection
{
    /** @var HttpField[] */
    protected $httpFields;

    /**
     * HttpFieldCollection constructor.
     *
     * @param HttpField[] $httpFields
     */
    public function __construct(array $httpFields = [])
    {
        $this->httpFields = [];
        foreach ($httpFields as $httpField) {
            $this->httpFields[$httpField->getName()] = $httpField;
        }
    }

    public function add(HttpField $obj): void
    {
        if (array_key_exists($obj->getName(), $this->httpFields)) {
            if (!is_array($this->httpFields[$obj->getName()])) {
                $firstValue = $this->httpFields[$obj->getName()];
                $this->httpFields[$obj->getName()] = [];
                $this->httpFields[$obj->getName()][] = $firstValue;
            }
            $this->httpFields[$obj->getName()][] = $obj;

            return;
        }
        $this->httpFields[$obj->getName()] = $obj;
    }

    /**
     * @throws HttpFieldNotFoundOnCollection
     */
    public function delete(string $key): void
    {
        $this->checkKeyExists($key);
        unset($this->httpFields[$key]);
    }

    /**
     * @throws HttpFieldNotFoundOnCollection
     */
    public function get(string $key): HttpField|array
    {
        $this->checkKeyExists($key);

        return $this->httpFields[$key];
    }

    public static function fromHttpFieldArray(array $httpFields): self
    {
        return new self($httpFields);
    }

    /**
     * @throws HttpFieldNotFoundOnCollection
     */
    private function checkKeyExists($key): void
    {
        if (!array_key_exists($key, $this->httpFields)) {
            throw new  HttpFieldNotFoundOnCollection('Field ' . $key . ' not found');
        }
    }
}
