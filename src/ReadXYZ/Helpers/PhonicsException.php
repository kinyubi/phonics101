<?php


namespace App\ReadXYZ\Helpers;


use App\ReadXYZ\Data\SystemLogData;
use Exception;

class PhonicsException extends Exception
{

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $log = new SystemLogData();

        $msg = "$message  $this->file:$this->line";
        $log->add('fatal', $this->getTraceAsString(), $msg);
    }
}
