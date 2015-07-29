<?php
/*
 * CRON SCRIPT: Simulate Bets
 * Runs every 10 minutes
 */
chdir(dirname(__FILE__));
require '../config.php';

/* Get list of upcoming matches
 ******************************************************************************/
$matches = Match::GetAll();
$users = $db->fetch("SELECT * FROM User WHERE id IN (42, 43, 44)", null, 'User');

foreach ($users as $user) {
    foreach ($matches as $match) {
        if (strtotime($match->start_time) > strtotime('now')) { // Upcoming matches

            $bet = new Bet();
            $bet->user_id = $user->id;
            $bet->match_id = $match->id;
            $bet->type = 1;
            $bet->team = mt_rand(0, 2);
            $bet_amount = mt_rand(10, $user->points / 10) / 10 * 10; // Random bet amount
            $bet->points_simple = $bet_amount;

            // Create the bet
            $bet->Create();
        }
    }
}