<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\LogLevel;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;

class SystemLogData extends AbstractData
{
    public function __construct()
    { parent::__construct('abc_system_log'); }

    public function _create()
    {
        $query = <<<EOT
CREATE TABLE `abc_system_log` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`timeStamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`level` ENUM('info','debug','warning','error','fatal') NOT NULL DEFAULT 'info',
	`trace` VARCHAR(8192) NULL DEFAULT NULL,
	`message` VARCHAR(1024) NOT NULL,
	PRIMARY KEY (`id`) ) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * @param string $level
     * @param string $trace
     * @param string $message
     * @throws PhonicsException
     */
    public function add(string $level, string $trace, string $message) {
        if (!LogLevel::isValid($level)) {
            throw new PhonicsException("$level is not a valid log level.");
        }
        $qLevel = $this->smartQuotes($level);
        $qTrace = $this->smartQuotes($trace);
        $qMessage = $this->smartQuotes($message);
        $values = "VALUES(NOW(),$qLevel,$qTrace,$qMessage)";
        $query = "INSERT INTO abc_system_log(timeStamp, level, trace, message) $values";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }
}
