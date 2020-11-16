<?php

use App\ReadXYZ\Data\TabTypesData;
use App\ReadXYZ\Helpers\Util;

require_once 'autoload.php';

$json = file_get_contents(Util::getReadXyzSourcePath('resources/tabTypes.json'));
$tabTypes = json_decode($json);
$data = new TabTypesData();
foreach ($tabTypes as $tabType) {
    $result = $data->insertOrUpdateStd($tabType);
    if ($result->failed()) exit($result->getErrorMessage());
}
