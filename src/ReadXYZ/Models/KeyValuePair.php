<?php

namespace App\ReadXYZ\Models;

use InvalidArgumentException;

class KeyValuePair
{
    protected string $key;
    /** @var mixed */
    protected $value;

    public function __construct(string $key, $value)
    {
        if (empty($key) or empty($value)) {
            throw new InvalidArgumentException('Empty parameters are not allowed.');
        }
        $this->key = $key;
        $this->value = $value;
    }

    public function addToArray(array &$array)
    {
        $array[$this->key] = $this->value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
