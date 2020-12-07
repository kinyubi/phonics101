<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\TableData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Helpers\PhonicsException;

class CrudAction
{
    private string  $tableName;
    private string  $primaryKey;
    private string  $action;
    private int     $index;
    private array   $data;
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();

        $this->tableName  = $_POST['tablename'] ?? '';
        $this->primaryKey = $_POST['primary_key'] ?? '';
        foreach ($_POST as $key => $value) {
            if (Util::startsWith_ci(['Update', 'Delete'], $key)) {
                $this->action = substr($key, 0, 6);
                $this->index  = intval(substr($key, 6));
            }
        }
        $this->data = $_POST['data'];
    }

// ======================== PUBLIC METHODS =====================
    public function handlePost()
    {
        if ( !$this->session->isStaff()) {
            throw new PhonicsException('Invalid operation for non-staff user.');
        }
        $tableData = new TableData($this->tableName);
        // TODO: finish this
    }

}
