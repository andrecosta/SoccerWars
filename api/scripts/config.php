<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Import libraries (Slim framework, PHPMailer, etc)
require '../vendor/autoload.php';

// Import models
require '../classes/DB.php';
require '../classes/User.php';
require '../classes/Team.php';
require '../classes/Match.php';
require '../classes/Token.php';
require '../classes/Mail.php';

date_default_timezone_set('Europe/Lisbon');