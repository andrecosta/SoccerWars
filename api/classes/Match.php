<?php

class Match {
    public $id;
    public $team_1;
    public $team_2;
    public $start_time;
    public $end_time;
    public $progress;

    /**
     * Get a match instance from an ID
     * @param int $id
     * @return Match|bool
     */
    static function Get($id) {
        $db = new DB();

        $data = ["id" => $id];

        if ($match = $db->fetch("SELECT * FROM `Match` WHERE id = :id", $data, 'Match')) {
            $match->progress = [
                $match->getProgress($match->team_1),
                $match->getProgress($match->team_2)
            ];
            $match->team_1 = Team::Get($match->team_1);
            $match->team_2 = Team::Get($match->team_2);
            return $match;
        } else
            return false;
    }

    /**
     * Get all matches
     * @return Match[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($matches = $db->fetch("SELECT * FROM `Match`", null, 'Match')) {
            foreach ($matches as &$match) {
                $match->progress = [
                    $match->getProgress($match->team_1),
                    $match->getProgress($match->team_2)
                ];
                $match->team_1 = Team::Get($match->team_1);
                $match->team_2 = Team::Get($match->team_2);
            }
            return $matches;
        } else
            return false;
    }

    function getProgress($team_id) {
        $db = new DB();

        $data = [
            "match_id" => $this->id,
            "team_id" => $team_id,
        ];

        if ($progress = $db->fetch("SELECT * FROM MatchProgress WHERE match_id = :match_id AND team_id = :team_id", $data))
            return $progress;
        else
            return false;
    }

    /*function GetTeams() {
        $db = new DB();

        if ($teams = $db->fetch("SELECT * FROM MatchPr WHERE", null))
            foreach ($matches as $match) {
                $teams = Team::Get($match->id)
            }
        return $matches;
    else
        return false;
    }*/

    /**
     * Create a new match and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        $data = [
            "team_1" => $this->team_1,
            "team_2" => $this->team_2,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time
        ];

        if ($match_id = $db->modify("INSERT INTO `Match` (team_1, team_2, start_time, end_time)
                                    VALUES (:team_1, :team_2, :start_time, :end_time)", $data)) {
            return $match_id;
        } else
            return false;
    }

    /*function setStatus($status) {
        $this->status = $status;
        $db = new DB();
        $data = [
            "id" => $this->id,
            "status" => $this->status
        ];
        if ($db->modify("UPDATE User SET status = :status WHERE id = :id", $data))
            return true;
        else
            return false;
    }*/
}