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

        if ($match = $db->fetch("SELECT * FROM `Match` WHERE id = :id", $data, 'Match')[0]) {
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

        if ($matches = $db->fetch("SELECT * FROM `Match` ORDER BY start_time DESC", null, 'Match')) {
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

    /**
     * Populate match progress
     * @param int $team_id
     * @return array|bool
     */
    function getProgress($team_id) {
        $db = new DB();

        $data = [
            "match_id" => $this->id,
            "team_id" => $team_id,
        ];

        if ($progress = $db->fetch("SELECT * FROM MatchProgress WHERE match_id = :match_id AND team_id = :team_id", $data)[0])
            return $progress;
        else
            return false;
    }

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

            // Initialize match progress for the 2 teams
            $db->modify("INSERT INTO MatchProgress (team_id, match_id) VALUES (:team_id, :match_id)",
                ["team_id" => $this->team_1, "match_id" => $match_id]);
            $db->modify("INSERT INTO MatchProgress (team_id, match_id) VALUES (:team_id, :match_id)",
                ["team_id" => $this->team_2, "match_id" => $match_id]);

            $this->progress = [
                $this->getProgress($this->team_1),
                $this->getProgress($this->team_2)
            ];

            return $match_id;
        } else
            return false;
    }

    /**
     * Create a comment on the match
     * @return int|bool
     */
    function insertComment($user_id, $text) {
        $db = new DB();

        $data = [
            "user_id" => $user_id,
            "match_id" => $this->id,
            "text" => $text
        ];

        if ($comment_id = $db->modify("INSERT INTO UserComment (user_id, match_id, text) VALUES (:user_id, :match_id, :text)", $data)) {
            return $comment_id;
        } else
            return false;
    }

    /**
     * Get all the comments from the match
     * @return array|bool
     */
    function getComments() {
        $db = new DB();

        $data = ['match_id' => $this->id];

        if ($comments = $db->fetch("SELECT id, user_id, text, created_at FROM UserComment WHERE match_id = :match_id AND deleted = 0", $data)) {
            foreach ($comments as &$comment) {
                $user = User::Get($comment['user_id']);
                $comment['user']['name'] = $user->name;
                $comment['user']['avatar'] = $user->avatar['small'];
            }
            return $comments;
        } else
            return false;
    }
}