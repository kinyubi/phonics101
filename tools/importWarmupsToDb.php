<?php

use App\ReadXYZ\Data\WarmupData;

require 'autoload.php';

$warmupData = new WarmupData();

$warmupData->importJson();

