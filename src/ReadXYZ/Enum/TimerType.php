<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class TimerType extends Enum
{
    const FLUENCY = 'fluency';
    const TEST = 'test';

    public function getSqlFieldName(): string
    {
        return $this->getValue() . 'Times';
    }
}

