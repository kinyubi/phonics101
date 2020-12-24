<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

class OldUserData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_Users', 'uuid', DbVersion::READXYZ0_1);
    }

    /**
     * @return array|false|int|mixed|stdClass|string|null
     * @throws PhonicsException
     */
    public function getAll()
    {
        $query = "SELECT uuid, UserName FROM abc_Users";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * @return array
     * @throws PhonicsException
     */
    public function getUsersWithoutStudents(): array
    {
        $query = "SELECT UserName FROM abc_users WHERE UserName NOT IN (SELECT trainer1 FROM abc_student) ";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }
}
