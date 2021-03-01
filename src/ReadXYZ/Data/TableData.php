<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Regex;

/**
 * Class TableData
 * @package App\ReadXYZ\Data
 * This class is able to analyze table structures and fields.
 */
class TableData extends AbstractData
{
    private array $fields      = [];
    private array $autoUpdates = [
        'abc_students|dateLastAccessed',
        'abc_trainers|dateModified',
        'abc_trainers|dateLastAccessed'
    ];
    private int   $totalSize;

    public function __construct(string $tableName, string $enumBool = BoolEnumTreatment::KEEP_AS_Y_N, $version='')
    {
        if ( ! BoolEnumTreatment::isValid($enumBool)) {
            $enumBool = BoolEnumTreatment::KEEP_AS_Y_N;
        }

        $db0       = ['abc_student', 'abc_user_mastery', 'abc_users'];
        if ($version) {
            $dbVersion = $version;
        } else {
            $dbVersion = in_array($tableName, $db0) ? DbVersion::READXYZ0_1 : DbVersion::READXYZ0_PHONICS;
        }


        parent::__construct($tableName, 'Field', $dbVersion);
        $query        = "SHOW COLUMNS FROM $tableName";
        $fieldRecords = $this->throwableQuery($query, QueryType::ASSOCIATIVE_ARRAY, $enumBool);
        $totalSize    = $this->getTotalSize($fieldRecords);

        foreach ($fieldRecords as $record) {
            $fieldName = $record['Field'];
            $size      = $this->clampSize($this->extractNumber($record['Type']));
            $percent   = number_format(85.00 * $size / $totalSize, 2) . '%';
            if ($record['Key'] == 'PRI') {
                $this->primaryKey = $fieldName;
            }
            $fieldInfo      = (object)[
                'name'        => $fieldName,
                'read_only'   => ($record['Extra'] == 'auto_increment') ? 'readonly' : '',
                'isKey'       => ! empty($record['Key']),
                'default'     => $record['Default'],
                'width'       => $percent,
                'enum_bool'   => ($record['Type'] == 'ENUM') && in_array($record['Default'], ['Y', 'N']),
                'isJson'      => ($record['Type'] == 'mediumtext'),
                'auto_update' => in_array("$tableName|$fieldName", $this->autoUpdates),
            ];
            $this->fields[$fieldName] = $fieldInfo;
        }
        $this->totalSize = $totalSize;
    }

// ======================== PUBLIC METHODS =====================
    public function getAll(): array
    {
        $query = "SELECT * FROM {$this->tableName}";
        return $this->throwableQuery($query, QueryType::ASSOCIATIVE_ARRAY, BoolEnumTreatment::KEEP_AS_Y_N);
    }

    /**
     * t3 arguments are passed into table_crud - tablename, fields and data
     */
    public function getTwigArguments(): array
    {
        $data = $this->getAll();
        return [
            'tablename' => $this->tableName,
            'fields'    => $this->fields, // [ 'fieldName' => fieldInfo]
            'data'      => $data,         // array of records. Each record is ['fieldName' => value, ...]
            'primary'   => $this->primaryKey
        ];
    }

// ======================== PRIVATE METHODS =====================

    /**
     * For crud operations we need to make a best guess for field width
     * @param $number
     * @return mixed
     */
    private function clampSize($number)
    {
        return clamp($number, 12, 120);
    }

    /**
     * extracts the size from the DataType field
     * @param string $field
     * @return int
     */
    private function extractNumber(string $field): int
    {
        $pos = strpos($field, '(');
        $fieldType = ($pos === false) ? $field : substr($field, 0, $pos);
        switch(strtolower($fieldType)) {
            case 'date':
            case 'enum':
                return 12;
            case 'mediumtext':
                return 120;
            default:
                return ($pos === false) ? 12 : Regex::extractSqlFieldLength($field);
        }
    }

    private function getTotalSize(array $fieldRecords): int
    {
        $totalSize = 0;
        foreach ($fieldRecords as $record) {
            $size      = $this->extractNumber($record['Type']);
            $totalSize += $this->clampSize($size);
        }
        return $totalSize;
    }
}
