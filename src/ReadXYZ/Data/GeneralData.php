<?php


namespace App\ReadXYZ\Data;

use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;

class GeneralData extends AbstractData
{
    private const NOT_LIKE = ' table_name NOT LIKE ';
    private const IS_LIKE  = ' table_name LIKE ';

    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('', '', $dbVersion);
    }

    /**
     * @param string $db
     * @return bool
     * @throws PhonicsException
     */
    public function doesDatabaseExist(string $db): bool
    {
        return $this->throwableQuery("SHOW DATABASES LIKE '$db' ", QueryType::EXISTS);
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws PhonicsException
     */
    public function doesTableExist(string $tableName): bool
    {
        return $this->throwableQuery("SHOW TABLES LIKE '$tableName' ", QueryType::EXISTS);
    }

    /**
     * @return array
     * @throws PhonicsException
     */
    public function getAllTables(): array
    {
        return $this->throwableQuery("SHOW TABLES LIKE 'abc_%'", QueryType::SCALAR_ARRAY);
    }

    /**
     * @return array
     * @throws PhonicsException
     */
    public function getAllViews(): array
    {
        return $this->throwableQuery("SHOW TABLES LIKE 'vw_%'", QueryType::SCALAR_ARRAY);
    }

    /**
     * @return array
     * @throws PhonicsException
     */
    public function getDynamicTables(): array
    {
        $data  = new GeneralData();
        $query = <<<EOT
            SELECT table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = '#DB#' AND
            #TL# 'abc%' AND (#TL# 'abc_student%' OR #TL# 'abc_train%' OR #TL# 'abc_word%')
EOT;
        $query = $this->insertConstants($query);
        return $data->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @return array
     * @throws PhonicsException
     */
    public function getFixedTables(): array
    {
        $data  = new GeneralData();
        $query = <<<EOT
            SELECT table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = '#DB#' 
            AND table_name LIKE 'abc%' AND (#TNL# 'abc_student%' AND #TNL# 'abc_train%' and #TNL# 'abc_word%')
EOT;
        $query = $this->insertConstants($query);
        return $data->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @throws PhonicsException
     */
    private function notImplemented()
    {
        throw new PhonicsException("Invalid method for this class.");
    }

    // ======================== PRIVATE METHODS =====================
    private function insertConstants(string $statement): string
    {
        $searches = ['#TNL#', '#TL#', '#DB#'];
        $replaces = [self::NOT_LIKE, self::IS_LIKE, $this->dbName];
        return str_replace($searches, $replaces, $statement);
    }


    // ------------ DELETED FUNCTIONS -----------------------------------
    public function deleteOne($keyValue): void { $this->notImplemented(); }
    public function updateOne($keyValue, string $fieldName, $newValue): void { $this->notImplemented(); }
    protected function baseDelete(string $where, int $foreignKeyChecks=0): int { $this->notImplemented(); return 0;}
    public function truncate(): int { $this->notImplemented(); return 0;}

}
