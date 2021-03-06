<?php

class Team {
    public $id;
    public $name;
    public $crest;
    public $rank;

    /**
     * Get a team instance from an ID
     * @param int $id
     * @return Team|bool
     */
    static function Get($id) {
        $db = new DB();

        $data = ["id" => $id];

        if ($team = $db->fetch("SELECT * FROM Team WHERE id = :id", $data, 'Team')[0]) {
            return $team;
        } else
            return false;
    }

    /**
     * Get all teams
     * @return Team[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($teams = $db->fetch("SELECT * FROM Team ORDER BY rank DESC", null, 'Team')) {
            return $teams;
        } else
            return false;
    }

    /**
     * Create a new team and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "crest" => $this->crest,
        ];

        if ($team_id = $db->modify("INSERT INTO Team (id, name, crest) VALUES (:id, :name, :crest)", $data)) {
            return $team_id;
        } else
            return false;
    }

    /**
     * Updates a team's rank
     * @param int $team_id
     * @param int $points
     */
    static function UpdateRank($team_id, $points) {
        $db = new DB();

        $data = [
            "team_id" => $team_id,
            "points" => round($points)
        ];

        $db->modify("UPDATE Team SET rank = rank + :points WHERE id = :team_id", $data);
    }
}