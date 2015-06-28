<?php
// Localhost / Wamp database
define("HOST", "localhost");
define("USER", "root");
define("PASS", "");

// Credentials to a working online database to test in case the localhost is not behaving
//define("HOST", "db.drymartini.eu");
//define("USER", "soccerwars");
//define("PASS", "mikas4ever");

define("DB"  , "soccerwars");

$connection = mysqli_connect(HOST, USER, PASS, DB);

// TODO: Future implementation using classes