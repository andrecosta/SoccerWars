<?php
$connection = mysqli_connect("localhost", "root", "", "soccerwars");

$seasons_array = fetch_json("http://api.football-data.org/alpha/soccerseasons");

// Fetch seasons
foreach ($seasons_array as $season) {
    //var_dump($season);
    $teamLinks[] = $season["_links"]["teams"]["href"];
}

// Fetch teams
foreach ($teamLinks as $teamLink) {
    $teams_array = fetch_json($teamLink);
    foreach ($teams_array as $teams) {
        for ($i = 0; $i < sizeof($teams); $i++){
            $team_id = end(explode('/', $teams[$i]["_links"]["self"]["href"]));
            $team_name = $teams[$i]["name"]."<br>";
            mysqli_query($connection, "INSERT INTO Team (Name) VALUES ('$team_name')");

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
                    mysqli_query($connection, "INSERT INTO Player (Name, BirthDate, Country) VALUES ('$name', '$birthdate', '$nationality')");
                    mysqli_query($connection, "INSERT INTO TeamComposition (TeamID, PlayerID, ShirtNumber, Position) VALUES ($team_id, $player_id, $shirtnumber, '$position')");
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