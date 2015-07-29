<?php
/*
 * CRON SCRIPT: Check Bets
 * Runs every minute
 */
chdir(dirname(__FILE__));
require '../config.php';

/* Get list of ended matches
 ******************************************************************************/
$matches = Match::GetAll();

foreach ($matches as $match) {
    if (strtotime($match->end_time) < strtotime('now')) {

        $team1_id = $match->progress[0]['team_id'];
        $team1_goals = $match->progress[0]['goals'];
        $team1_yellow_cards = $match->progress[0]['yellow_cards'];
        $team1_redcards = $match->progress[0]['redcards'];
        $team1_defenses = $match->progress[0]['defenses'];
        $team2_id = $match->progress[1]['team_id'];
        $team2_goals = $match->progress[1]['goals'];
        $team2_yellow_cards = $match->progress[1]['yellow_cards'];
        $team2_redcards = $match->progress[1]['redcards'];
        $team2_defenses = $match->progress[1]['defenses'];

        $team1_rank = 0;
        $team2_rank = 0;

        if ($bets = Bet::GetByMatch($match->id)) {
            foreach ($bets as $bet) {
                if ($bet->result == null) {
                    $user = User::Get($bet->user_id);
                    $result = null;

                    // Simple bet
                    if ($bet->type == 1) {
                        if ($team1_goals > $team2_goals) {
                            $result = 1;
                            $team1_rank += $bet->points_total;
                            $team2_rank -= $bet->points_total;
                        } elseif ($team1_goals == $team2_goals)
                            $result = 0;
                        elseif ($team1_goals > $team2_goals) {
                            $result = 2;
                            $team2_rank += $bet->points_total;
                            $team1_rank -= $bet->points_total;
                        }

                        if ($bet->team == $result)
                            resolveBet(1, $bet, $user, $bet->points_simple * 3);
                        else
                            resolveBet(0, $bet, $user);

                    } // Advanced bet
                    elseif ($bet->type == 2) {
                        if ($bet->team == 1) {
                            if ($team1_goals > $team2_goals)
                                resolveBet(1, $bet, $user, $bet->points_goals * 2);
                            if ($team1_yellow_cards > $team2_yellow_cards)
                                resolveBet(1, $bet, $user,$bet->points_yellowcards * 2);
                            if ($team1_redcards > $team2_redcards)
                                resolveBet(1, $bet, $user, $bet->points_redcards * 2);
                            if ($team1_defenses > $team2_defenses)
                                resolveBet(1, $bet, $user, $bet->points_defenses * 2);
                            if ($bet->result == null)
                                resolveBet(0, $bet, $user);
                            else {
                                $team1_rank += $bet->points_total;
                                $team2_rank -= $bet->points_total;
                            }
                        } elseif ($bet->team == 2) {
                            if ($team2_goals > $team1_goals)
                                resolveBet(1, $bet, $user, $bet->points_goals * 2);
                            if ($team2_yellow_cards > $team1_yellow_cards)
                                resolveBet(1, $bet, $user, $bet->points_yellowcards * 2);
                            if ($team2_redcards > $team1_redcards)
                                resolveBet(1, $bet, $user, $bet->points_redcards * 2);
                            if ($team2_defenses > $team1_defenses)
                                resolveBet(1, $bet, $user, $bet->points_defenses * 2);
                            if ($bet->result == null)
                                resolveBet(0, $bet, $user);
                            else {
                                $team2_rank += $bet->points_total;
                                $team1_rank -= $bet->points_total;
                            }
                        }
                    }
                }
            }
        }
        // Update team ranks
        if ($team1_rank != 0) Team::UpdateRank($team1_id, $team1_rank / 100);
        if ($team2_rank != 0) Team::UpdateRank($team2_id, $team2_rank / 100);
    }
}

function resolveBet($result, $bet, $user, $points = null) {
    if ($result == 1) {
        $user->givePoints($points);
        $user->awardBadge(2);
    }
    $bet->setResult($result);
}