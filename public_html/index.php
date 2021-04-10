<?php

/**
 * using 1.0407.0 versioning for  phonics css and js.
 *
 */

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Models\RouteMe;
use Symfony\Component\ErrorHandler\Debug;

//require dirname(__DIR__).'/config/bootstrap.php';
require __DIR__ . '/autoload.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
    Debug::enable();
}
Util::checkCache();
Session::sessionContinue();
RouteMe::parseRoute();

