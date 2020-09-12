<?php


namespace ReadXYZ\Database;


class OneTimePass
{
    private string $tableName = 'abc_onetime_pass';

    private string $createQuery = <<<EOT
CREATE TABLE IF NOT EXISTS `abc_onetime_pass` (
  `hash` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;

    private PhonicsDb $phonicsDb;

    public function __construct()
    {
        $this->phonicsDb = new PhonicsDb();
        $result = $this->phonicsDb->queryAndGetCount("SHOW TABLES LIKE 'abc_onetime_pass'");
        $count = $result->wasSuccessful() ? $result->getResult() : 0;
        if ($count == 0) {
            $this->phonicsDb->queryStatement($this->createQuery);
        }
    }

    /**
     * Creates a one-time password for a user and puts it in the abc_onetime_pass table
     * @param string $username
     * @return string
     */
    public function getOTP(string $username): string
    {
        $otp = md5($username . strval(time()));
        $query = "INSERT INTO {$this->tableName} VALUES ('$otp','$username')";
        if (false === $this->phonicsDb->queryStatement($query)) {
            error_log("unable to create OTP. " . $this->phonicsDb->getErrorMessage());
        }
        return $otp;
    }

    /**
     * Decodes a one-time password and returns the associated username
     * @param string $otp
     * @return string
     */
    public function decodeOTP(string $otp): string
    {
        $query = "SELECT username FROM abc_onetime_pass WHERE hash = '$otp' ";
        $result = $this->phonicsDb->queryAndGetScalar($query);
        $user = $result->wasSuccessful() ? $result->getResult() : '';
        if ($user) {
            $query = "DELETE FROM abc_onetime_pass WHERE hash = '$otp'";
            $this->phonicsDb->queryStatement($query);
        }
        return $user;
    }

}
