<?php


namespace App\ReadXYZ\Data;


/**
 * Class UserData
 * @package App\ReadXYZ\Data
 * Provides routines to interact with abc_Users table
 */
class UserData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_users');
    }

    /**
     * Gets the userId associated with a given user name. Returns empty string if not found
     * @param string $username
     * @return string the userId if found, otherwise the empty string
     */
    public function getUserId(string $username): string
    {
        $query = 'SELECT uuid FROM abc_Users WHERE UserName = ?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('s', $username);
        $statement->execute();
        $statement->bind_result($userId);
        $statement->fetch();
        $statement->close();
        return $userId ?? ''; // fetch returns null if nothing found
    }

    public function getUserName(string $userId): string
    {
        $query = 'SELECT UserName FROM abc_Users WHERE uuid = ?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('s', $userId);
        $statement->execute();
        $statement->bind_result($userName);
        $statement->fetch();
        $statement->close();
        return $userName ?? ''; // fetch returns null if nothing found
    }

}
