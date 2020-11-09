<?php

namespace App\ReadXYZ\Data;

use mysqli;

/**
 * Class DbResult.
 *
 * @package ReadXYZ\Database
 */
class DbResult
{
    private bool $success; //bool
    /**
     * @var mixed
     */
    private $result;
    private string $message;

    public function __construct($value, bool $success, string $message)
    {
        $this->success = $success;
        $this->result = $value;
        $this->message = $message;
    }

    /**
     * a static function that you can use to pass back an error result.
     *
     * @param string $msg
     *
     * @return DbResult
     */
    public static function badResult(string $msg)
    {
        return new self(null, false,  $msg);
    }

    /**
     * a static function that encapsulates a mysqli query result.
     *
     * @param mixed $value
     *
     * @return DbResult
     */
    public static function goodResult($value)
    {
        return new self($value, true, '');
    }

    public function wasSuccessful(): bool
    {
        return $this->success;
    }

    public function failed(): bool
    {
        return !$this->success;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
