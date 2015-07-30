<?php
/*
 * CRON SCRIPT: Simulate Bets
 * Runs every 30 minutes
 */
chdir(dirname(__FILE__));
require '../config.php';

$matches = Match::GetAll();

// Get the users accounts that represent the automated bots
$users = $db->fetch("SELECT * FROM User WHERE id IN (42, 43, 44, 28)", null, 'User');

foreach ($users as $user) {
    foreach ($matches as $match) {
        if (strtotime($match->start_time) > strtotime('now')) { // Filter by upcoming matches only

            // Setup the bet
            $bet = new Bet();
            $bet->user_id = $user->id;
            $bet->match_id = $match->id;
            $bet->type = 1;
            $bet->team = mt_rand(0, 2);
            $bet_amount = mt_rand(10, $user->points / 100) / 10 * 10; // Random bet amount up to a tenth of the user's balance
            $bet->points_simple = $bet_amount;

            // Create the bet
            $bet->Create();
        }
    }
}