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
    
?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link href="main.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <a id="nav-matches" href="#">» Matches</a>
            <a id="nav-users" href="#">» Users</a>
        </nav>
    </header>

    <div id="message"></div>

    <section id="section-matches">
        <form id="create-match">
            <label>Team 1</label><input type="text" name="team_1"><br>
            <label>Team 2</label><input type="text" name="team_2"><br>
            <label>Start time</label><input type="datetime-local" name="start_time"><br>
            <label>End time</label><input type="datetime-local" name="end_time"><br>
            <input type="submit" value="Create match">
        </form>
    </section>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
