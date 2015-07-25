<?php

class Player {
    public $id;
    public $name;
    public $rank;

    /**
     * Get a player instance from an ID
     * @param int $id
     * @return Player|bool
     */
    static function Get($id) {
        $db = new DB();

        $data = ["id" => $id];

        if ($player = $db->fetch("SELECT * FROM PlayerTeam WHERE id = :id", $data, 'Player')) {
            return $player;
        } else
            return false;
    }

    /**
     * Get all players
     * @return Player[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($players = $db->fetch("SELECT * FROM Player", null, 'Player')) {
            return $players;
        } else
            return false;
    }

    /**
     * Create a new player and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        $data = [
            "name" => $this->name,
        ];

        if ($player_id = $db->modify("INSERT INTO Player (name) VALUES (:name)", $data)) {
            return $player_id;
        } else
            return false;
    }
}