<?php
chdir(dirname(__FILE__));
require 'config.php';

/* Get list of existing teams
 ******************************************************************************/
$teams = $db->fetch("SELECT id FROM Team", null, 'Team');
var_dump($teams);

/* Assign two random opposing teams
 ******************************************************************************/
$team_1 = $teams[mt_rand(0, count($teams) - 1)]->id;
do $team_2 = $teams[mt_rand(0, count($teams) - 1)]->id;
while ($team_2 == $team_1);

/* Generate match start dates in the near future and duration
 ******************************************************************************/
$format = 'Y-m-d H:i:s';
$duration = 5; // Duration of the match (in minutes)
//$start_time = date($format, strtotime("now +".mt_rand(0, 12)." hours"));
$start_time = date($format, strtotime("now +10 minutes"));
$end_time = date($format, strtotime("$start_time +$duration minutes"));

/* Create the match
 ******************************************************************************/
$match = new Match();
$match->team_1 = $team_1;
$match->team_2 = $team_2;
$match->start_time = $start_time;
$match->end_time = $end_time;
$match->Create();
var_dump($match);
