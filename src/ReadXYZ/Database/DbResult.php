<?php

namespace ReadXYZ\Database;

use mysqli;

/**
 * Class DbResult.
 *
 * @package ReadXYZ\Database
 */
class DbResult
{
    private bool $success; //bool
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
     * @param mysqli $conn
     *
     * @return DbResult
     */
    public static function BadResult($conn)
    {
        return new self(null, false, $conn->error);
    }

    /**
     * a static function that encapsulates a mysqli query result.
     *
     * @param mixed $value
     *
     * @return DbResult
     */
    public static function GoodResult($value)
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
