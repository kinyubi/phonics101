<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class MasteryLevel extends Enum
{
    const NONE = 0;
    const ADVANCING = 1;
    const MASTERED = 2;

    public function getSqlValue()
    {
        return strtolower($this->getKey());
    }

    public static function getSqlValues()
    {
        return array_map('strtolower', self::keys());
    }

    /**
     * @return scalars instead of MasteryLevelObjects
     */
    public static function getValues(): array
    {
        $objects = parent::values();
        $values = [];
        foreach($objects as $object) $values[] = $object->getValue();
        return $values;
    }

}
