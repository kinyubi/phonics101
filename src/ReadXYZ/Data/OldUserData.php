<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Sql;

class OldUserData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students', 'studentid', Sql::READXYZ0_1);
    }

    public function getAll()
    {
        $query = "SELECT uuid, UserName FROM abc_Users";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    public function getUsersWithoutStudents(): array
    {
        $query = "SELECT UserName FROM abc_users WHERE UserName NOT IN (SELECT trainer1 FROM abc_student) ";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }
}
