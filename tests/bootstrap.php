<?php

/**
 * This is the bootstrap loader for our unit tests as defined in /phpunit.xml.dist
 */
use Symfony\Component\Dotenv\Dotenv;

// require dirname(__DIR__).'/vendor/autoload.php';

if (!defined('TESTING_IN_PROGRESS')) {
    define('TESTING_IN_PROGRESS', true);
}
require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
