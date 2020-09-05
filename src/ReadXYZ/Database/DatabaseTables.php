<?php


namespace ReadXYZ\Database;


use ReadXYZ\Models\Document;

class DatabaseTables
{
    private static DatabaseTables $instance;
    
    private array $coreTables = array();
    private array $allTables = array();

    public static function getInstance()
    {
        if (!isset(self::$instance)) {self::$instance = new DatabaseTables();}
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->coreTables[] = SystemLog::getInstance();
        $this->coreTables[] = StudentTable::getInstance();
        $this->coreTables[] = StudentEventTable::getInstance();
        $this->coreTables[] = TrainingLog::getInstance();
        $this->coreTables[] = Projects::getInstance();
        $this->coreTables[] = LessonResults::getInstance();
        $this->coreTables[] = CRM::getInstance(); // customer relationship management
        $this->coreTables[] = Users::getInstance();
    }

    public function getCoreTables() : array {return $this->coreTables;}

    /**
     * @throws \Exception if SHOW TABLES query fails
     */
    public function verifyTables() : void
    {
        $db = new DbConnect();
        $resultSet = $db->fetch_array('SHOW TABLES', MYSQLI_NUM);
        if (empty($resultSet)) throw new \Exception("SHOW TABLES query failed.");
        foreach ($resultSet as  $record) {
            foreach ($record as $key => $tableName)
            $this->allTables[] = strtolower($tableName); //lowercase names work regardless of OS case sensitivity
        }

        foreach ($this->coreTables as $coreTable) {
            if ( ! in_array(strtolower($coreTable->tableName), $this->allTables)) {
                $document = Document::getInstance();
                $document->appendToSystemMessage("{$coreTable->tableName} doesn't exist, trying to create...");
                //$coreTable->create();
            }

        }

        $db->close();
    }
}