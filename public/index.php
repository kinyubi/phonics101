<?php

/**
 * using 0.1229.1 versioning for  phonics css and js.
 *
 */
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Models\RouteMe;
use Symfony\Component\ErrorHandler\Debug;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
    Debug::enable();
}
Session::sessionContinue();
RouteMe::parseRoute();
