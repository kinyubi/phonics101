<?php namespace ReadXYZ\Database;

interface IBasicTableFunctions
{
    public function getListOfTables();
    public function create();
    public function drop();
    public function insert($cargo, $key);
    public function getCargoByKey($key);
    public function deleteByKey($key);
    public function updateByKey($key, $cargo);
    public function getAllCargoByWhere($where, $order);
    public function deleteProject($project); // clean out from that table
}