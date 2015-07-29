<?php
/*
 * CRON SCRIPT: Create Match
 * Runs every 10 minutes
 */
chdir(dirname(__FILE__));
require '../config.php';

$number_of_matches = 5;

/* Get list of existing teams
 ******************************************************************************/
$teams = $db->fetch("SELECT id FROM Team", null, 'Team');
//var_dump($teams);

for ($i = 0; $i < $number_of_matches; $i++) {

    /* Assign two random opposing teams
     ******************************************************************************/
    $team_1 = $teams[mt_rand(0, count($teams) - 1)]->id;
    // Prevent second team from being the same as the first one
    do $team_2 = $teams[mt_rand(0, count($teams) - 1)]->id;
    while ($team_2 == $team_1);

    /* Generate match start dates in the near future and duration
     ******************************************************************************/
    $format = 'Y-m-d H:i:s';
    $duration = 10; // Duration of the match (in minutes)
    $start_time = date($format, strtotime("now +".mt_rand(5, 25)." minutes"));
    $end_time = date($format, strtotime("$start_time +$duration minutes"));

    /* Create the match
     ******************************************************************************/
    $match = new Match();
    $match->team_1 = $team_1;
    $match->team_2 = $team_2;
    $match->start_time = $start_time;
    $match->end_time = $end_time;
    $match->Create();
    //var_dump($match);

}