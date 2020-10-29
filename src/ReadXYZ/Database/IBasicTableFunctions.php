<?php namespace App\ReadXYZ\Database;

interface IBasicTableFunctions
{
    public function insert($cargo, $key);
    public function getCargoByKey($key);
    public function updateByKey($key, $cargo);
    public function getAllCargoByWhere($where, $order);
}
