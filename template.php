<?php
session_start();
include("db.php");

function template_header($page) {
?><!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <title>SoccerWars</title>
    <link href="css/main.css" rel="stylesheet">
</head>
<body class="page-<?=$page?>">
    <header>
        <nav id="top-menu">
            <ul>
                <?php if (!isset($_SESSION['uid'])) { ?>
                <li><a href="register.php">Create Account</a></li>
                <li><a href="login.php">Login</a></li>
                <?php } else { ?>
                <li><a href="#">My Profile (<?=$_SESSION["username"]?>)</a></li>
                <li><a href="logout.php">Logout</a></li>
                <?php } ?>
            </ul>
        </nav>

    </header>

    <nav id="main-menu">
        <ul>
            <li><a class="nav-dashboard" href="index.php">Dashboard</a></li>
            <li><a class="nav-teams" href="#">Teams</a></li>
            <li><a class="nav-matches" href="#">Matches</a></li>
            <li><a class="nav-bets" href="#">My Bets</a></li>
        </ul>
    </nav>

    <main>
<?php }

function template_footer() { ?>
    </main>
</body>
</html>
<?php }