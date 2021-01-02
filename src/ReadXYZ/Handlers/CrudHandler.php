<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Data\TableData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;

class CrudHandler
{


    public function __construct()
    {



    }

// ======================== PUBLIC METHODS =====================
    public static function handlePost()
    {
        $tableName =  $_POST['tablename'] ?? '';
        $primaryKey = $_POST['primary_key'] ?? '';
        $action = [];
        $index = [];
        foreach ($_POST as $key => $value) {
            if (Util::startsWith_ci(['Update', 'Delete'], $key)) {
                $action[] = substr($key, 0, 6);
                $index[]  = intval(substr($key, 6));
            }
        }
        $data = $_POST['data'];
        if ( !Session::isStaff()) {
            throw new PhonicsException('Invalid operation for non-staff user.');
        }
        $tableData = new TableData($tableName);
        // TODO: finish this
    }

}
