<?php
/*
 * CRON SCRIPT: Simulate Match
 * Runs every minute
 */
chdir(dirname(__FILE__));
require '../config.php';

/* Get list of matches in progress
 ******************************************************************************/

$matches = Match::GetAll();

foreach ($matches as $match) {
    if (strtotime($match->start_time) < strtotime('now') && strtotime($match->end_time) > strtotime('now')) { // Live
        echo $match->id;
        $team1_id = $match->progress[0]['team_id'];
        $team1_goals = mt_rand(0, 99) < 50 ? 1 : 0;
        $team1_yellowcards = mt_rand(0, 99) < 25 ? 1 : 0;
        $team1_redcards = mt_rand(0, 99) < 15 ? 1 : 0;
        $team1_defenses = mt_rand(0, 99) < 50 ? 1 : 0;
        $team2_id = $match->progress[1]['team_id'];
        $team2_goals = mt_rand(0, 99) < 50 ? 1 : 0;
        $team2_yellowcards = mt_rand(0, 99) < 25 ? 1 : 0;
        $team2_redcards = mt_rand(0, 99) < 15 ? 1 : 0;
        $team2_defenses = mt_rand(0, 99) < 50 ? 1 : 0;

        $data = [
            'team_id' => $team1_id,
            'match_id' => $match->id,
            'goals' => $team1_goals,
            'yellowcards' => $team1_yellowcards,
            'redcards' => $team1_redcards,
            'defenses' => $team1_defenses
        ];

        $db->modify("UPDATE MatchProgress SET goals = goals + :goals, yellow_cards = yellow_cards + :yellowcards,
                     red_cards = red_cards + :redcards, defenses = defenses + :defenses
                     WHERE team_id = :team_id AND match_id = :match_id", $data);

        $data = [
            'team_id' => $team2_id,
            'match_id' => $match->id,
            'goals' => $team2_goals,
            'yellowcards' => $team2_yellowcards,
            'redcards' => $team2_redcards,
            'defenses' => $team2_defenses
        ];
        $db->modify("UPDATE MatchProgress SET goals = goals + :goals, yellow_cards = yellow_cards + :yellowcards,
                     red_cards = red_cards + :redcards, defenses = defenses + :defenses
                     WHERE team_id = :team_id AND match_id = :match_id", $data);
    }
}
