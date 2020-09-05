<?php

// file: processUserLogin.php
// validates the user login and then renders:
//   if user has multiple students, render student list
//   if user has one student, set student and render lesson list
// HTTP GET target
//   parameter: P1 username, P2 password (optional)
//   used by: login::login template

use ReadXYZ\Models\RouteMe;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Identity;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

// the main menu lists students, so we should not have
// a student associated with the user
$identity = Identity::getInstance();
$identity->clearIdentity();
$username = $_REQUEST['username'] ?? '';
$password = $_REQUEST['password'] ?? '';
$action = $_REQUEST['action'] ?? '';

$errorMessage = '';
$twigs = Twigs::getInstance();
if (empty($username) or empty($password) ) {
    echo $twigs->login('Username and password must both be provided.');
    exit;
}

$result = $identity->validateSignin($username, $password);
// validateSignin set the identity to the specified user to make him a valid user
if ($result->failed()) {
    echo $twigs->login($result->getErrorMessage());
    exit;
}

// if we get to here, we've logged in successfully

RouteMe::autoLogin();
