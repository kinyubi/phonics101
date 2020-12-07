<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Enum\Sql;

class TableData extends AbstractData
{
    private array $fields      = [];
    private array $autoUpdates = [
        'abc_students|dateLastAccessed',
        'abc_trainers|dateModified',
        'abc_trainers|dateLastAccessed'
    ];
    private int   $totalSize;

    public function __construct(
        string $tableName,
        string $enumBool = BoolEnumTreatment::KEEP_AS_Y_N)
    {
        if ( ! BoolEnumTreatment::isValid($enumBool)) {
            $enumBool = BoolEnumTreatment::KEEP_AS_Y_N;
        }

        $db0       = ['abc_student', 'abc_user_mastery', 'abc_users'];
        $dbVersion = in_array($tableName, $db0) ? Sql::READXYZ0_1 : Sql::READXYZ1_1;

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
                'isJson'      => ($record['Type'] == 'JSON'),
                'auto_update' => in_array("$tableName|$fieldName", $this->autoUpdates),
            ];
            $this->fields[] = $fieldInfo;
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
            'fields'    => $this->fields,
            'data'      => $data,
            'primary'   => $this->primaryKey
        ];
    }

// ======================== PRIVATE METHODS =====================
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
        if ($field == 'date') {
            return 10;
        }
        return Regex::extractSqlFieldLength($field);
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
