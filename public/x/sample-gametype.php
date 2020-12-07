<?php

use App\ReadXYZ\Data\TableData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\CrudTemplate;

require '../autoload.php';

if (isset($_POST['data'])) {
    $index = 0;
    $primaryKey = $_POST['primary_key'] ?? '';
    $tableName = $_POST['tablename'] ?? '';
    foreach($_POST as $key => $param) {
        if (Util::startsWith_ci('Update', $key)) {
            $index = intval(substr($key, 6));
            break;
        } elseif(Util::startsWith_ci('Delete', $key)) {
            $index = intval(substr($key, 6));
        }
    }

    exit();

}

$template = new CrudTemplate('abc_gametypes');
$template ->display();
