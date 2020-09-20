<?php

// file: processUserLogin.php
// validates the user login and then renders:
//   if user has multiple students, render student list
//   if user has one student, set student and render lesson list
// HTTP GET target
//   parameter: P1 username, P2 password (optional)
//   used by: login::login template

use ReadXYZ\Models\RouteMe;

require 'autoload.php';

RouteMe::processLogin($_REQUEST);
