<?php

use App\ReadXYZ\Data\GameTypesData;
use App\ReadXYZ\Helpers\Util;

require_once 'autoload.php';

$json = file_get_contents(Util::getReadXyzSourcePath('resources/gameTypes.json'));
$tabTypes = json_decode($json);
$data = new GameTypesData();
foreach ($tabTypes as $tabType) {
    $result = $data->insertOrUpdateStd($tabType);
    if ($result->failed()) exit($result->getErrorMessage());
    printf("Tab type {$tabType->gameTypeId} record created.\n");
}
