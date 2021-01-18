<?php


namespace App\ReadXYZ\CSV;


class S2MemberCsv extends CSV
{

    protected function __construct()
    {
        parent::__construct('s2_members.csv', 'Username');
    }

    /**
     * returns the singleton instance
     * @return S2MemberCsv
     */
    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }
}
