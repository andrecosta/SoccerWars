<?php
/*
 * CRON SCRIPT: Import Real World Data
 * Runs when needed
 *
 * NOTE: Be careful when running this script because of the remote API hourly request limit.
 * A more efficient method of importing the data should be considered
 */
chdir(dirname(__FILE__));
require '../config.php';

// Determine existing teams in the database
$current_teams = $db->fetch("SELECT id FROM Team", null, 'Team');
$current_team_ids = [];
foreach ($current_teams as $team)
    $current_team_ids[] = $team->id;

$seasons_array = fetch_json("http://api.football-data.org/alpha/soccerseasons");

// Fetch team links from seasons
foreach ($seasons_array as $season) {
    $teamLinks[] = $season["_links"]["teams"]["href"];
}

// Fetch teams
foreach ($teamLinks as $teamLink) {
    $teams_array = fetch_json($teamLink);
    foreach ($teams_array['teams'] as $team) {
        //var_dump($team);
        $team_id = end(explode('/', $team["_links"]["self"]["href"]));

        // Process team if it doesn't exist in the database
        if (!in_array($team_id, $current_team_ids)) {
            // Create team images
            $team_name = $team["name"] ?: 'NOIMAGE.jpg';
            $team_crestUrl = $team["crestUrl"];
            $image = file_get_contents($team_crestUrl);
            $ext = end(explode('.', $team_crestUrl));
            $filename = uniqid().".$ext";
            file_put_contents("../../static/crests/$filename", $image);

            // Crete the team
            $t = new Team();
            $t->id = $team_id;
            $t->name = $team_name;
            $t->crest = STATIC_URL . "/crests/$filename";
            $t->Create();

            // Fetch players
            $players_array = fetch_json("http://api.football-data.org/alpha/teams/$team_id/players");
            foreach ($players_array as $players) {
                for ($j = 0; $j < sizeof($players); $j++) {
                    $player_id = end(explode('/', $players[$j]["_links"]["self"]["href"]));
                    $name = $players["players"][$j]["name"];
                    $birthdate = $players["players"][$j]["dateOfBirth"];
                    $nationality = $players["players"][$j]["nationality"];
                    $shirtnumber = $players["players"][$j]["jerseyNumber"];
                    $position = $players["players"][$j]["position"];

                    // Create the players and team composition
                    $db->modify("INSERT INTO Player (Name, BirthDate, Country) VALUES ('$name', '$birthdate', '$nationality')");
                    $db->modify("INSERT INTO TeamComposition (TeamID, PlayerID, ShirtNumber, Position) VALUES ($team_id, $player_id, $shirtnumber, '$position')");
                }
            }
        }
    }
}

function fetch_json($uri) {
    $reqPrefs['http']['method'] = 'GET';
    $reqPrefs['http']['header'] = 'X-Auth-Token: 4d6e4e7ba4ae45989bbadb23fa629b60';
    $stream_context = stream_context_create($reqPrefs);
    $html = file_get_contents($uri, false, $stream_context);
    $json = json_decode($html, true);

    return $json;
}