<?php


// legacy 3rd-party library is set up old-style with all requires in libChart.php
require_once __DIR__ . '/functions.php';
if (!defined('XYZ_SRC_ROOT')) {
    define('XYZ_SRC_ROOT', str_replace('\\', '/',__DIR__ . '/'));
}
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', str_replace('\\', '/',dirname(dirname(__DIR__)) . '/'));
}

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', str_replace('\\', '/',dirname(__DIR__) . '/public/'));
}

// If the server is running globally define the base of phonics/101 in URL and file form.
if (isset($_SERVER['SERVER_NAME'])) {
    $prefix = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'https://' : 'http://';
    $root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    if ('/' !== substr($root, -1)) {
        $root .= '/';
    }

    if (!defined('PHONICS_URL')) {
        define('PHONICS_URL', $root);
    }
    // if we are running a unit test $_SERVER['SERVER_NAME'] won't be found but we still need these defined.
} else {
    if (!defined('PHONICS_URL')) {
        define('PHONICS_URL', 'http://phonics101.test/');
    }
}

try {
    require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
} catch (Throwable $e) {
    trigger_error('Composer vendor directory not found.');
    exit('Composer vendor directory not found.');
}


