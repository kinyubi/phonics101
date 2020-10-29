<?php

namespace App\ReadXYZ\Models;

class BoolWithMessage
{
    private bool $success;
    private string $message;

    private function __construct(bool $result, string $message = '')
    {
        $this->success = $result;
        $this->message = $message;
    }

    public static function badResult(string $message): BoolWithMessage
    {
        return new BoolWithMessage(false, $message);
    }

    public static function goodResult(): BoolWithMessage
    {
        return new BoolWithMessage(true, '');
    }

    public function wasSuccessful(): bool
    {
        return $this->success;
    }

    public function failed(): bool
    {
        return !$this->success;
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }
}
