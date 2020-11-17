<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class RecordType extends Enum
{
    const ASSOCIATIVE_ARRAY = 'associative_array';
    const STDCLASS_OBJECTS  = 'stdclass_objects';
    const SCALAR            = 'scalar';
    const SCALAR_ARRAY      = 'scalar_array';
    const RECORD_COUNT      = 'record_count';
    const AFFECTED_COUNT    = 'affected_count';
    const SINGLE_RECORD     = 'one_record';
    const SINGLE_OBJECT     = 'one_object';
    const STATEMENT         = 'statement';

    public static function get(string $value): RecordType
    {
        return new RecordType($value);
    }
}
