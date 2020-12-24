<?php

namespace App\ReadXYZ\Data;

use App\ReadXYZ\Models\BoolWithMessage;

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
     * Sets value to null, success to false and message to the query's error message.
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
     * Sets value to fetched query, success to true and message to empty string.
     *
     * @param mixed $value
     *
     * @return DbResult
     */
    public static function goodResult($value)
    {
        return new self($value, true, ($value == null) ? 'Not found' : '');
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

    public function getErrorMessage(): string
    {
        return $this->message;
    }

    public function notFound(): bool
    {
        return $this->result == null;
    }

    public function toBoolWithMessage(): BoolWithMessage
    {
        if ($this->success) {
            return BoolWithMessage::goodResult();
        } else {
            return BoolWithMessage::badResult($this->message);
        }
    }
}
