<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;

class OneTimePass extends AbstractData
{
    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function _create()
    {
        $query = <<<EOT
        CREATE TABLE IF NOT EXISTS `abc_onetime_pass` (
          `hash` varchar(50) NOT NULL DEFAULT '',
          `username` varchar(100) NOT NULL DEFAULT '',
          PRIMARY KEY (`hash`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_onetime_pass','hash', $dbVersion);
        $this->primaryKey = 'hash';
    }

    /**
     * Creates a one-time password for a user and puts it in the abc_onetime_pass table
     * @param string $username
     * @return DbResult
     * @throws PhonicsException on ill-formed SQL
     */
    public function add(string $username): DbResult
    {
        $otp = md5($username . strval(time()));
        $query = "INSERT INTO abc_onetime_pass VALUES ('$otp','$username')";
        return $this->query($query, QueryType::STATEMENT);
    }

    /**
     * Decodes a one-time password and returns the associated username. returns null if not found.
     * @param string $otp
     * @return DbResult
     * @throws PhonicsException on ill-formed SQL
     */
    public function decodeAndDelete(string $otp): DbResult
    {
        $query = "SELECT username FROM abc_onetime_pass WHERE hash = '$otp' ";
        $result = $this->query($query, QueryType::STATEMENT);
        $user = $result->wasSuccessful() ? $result->getResult() : '';
        if ($user) {
            $query = "DELETE FROM abc_onetime_pass WHERE hash = '$otp'";
            $this->query($query, QueryType::STATEMENT);
        }
        return $result;
    }

}
