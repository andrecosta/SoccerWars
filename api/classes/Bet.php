<?php

class Bet {
    public $id;
    public $user_id;
    public $match_id;
    public $type;
    public $team;
    public $points_simple;
    public $points_goals;
    public $points_yellowcards;
    public $points_redcards;
    public $points_defenses;
    public $points_total;
    public $created_at;
    public $result;

    /**
     * Get all bets
     * @return Bet[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($bets = $db->fetch("SELECT * FROM Bet ORDER BY created_at DESC", null, 'Bet')) {
            foreach ($bets as $bet) $bet->calculateTotal();
            return $bets;
        } else
            return false;
    }

    /**
     * Get all bets by user
     * @param int $user_id
     * @return Bet[]|bool
     */
    static function GetByUser($user_id) {
        $db = new DB();

        $data = ['user_id' => $user_id];

        if ($bets = $db->fetch("SELECT * FROM Bet WHERE user_id = :user_id ORDER BY created_at DESC", $data, 'Bet')) {
            foreach ($bets as $bet) $bet->calculateTotal();
            return $bets;
        } else
            return false;
    }

    /**
     * Get all bets by match
     * @param int $match_id
     * @return Bet[]|bool
     */
    static function GetByMatch($match_id) {
        $db = new DB();

        $data = ['match_id' => $match_id];

        if ($bets = $db->fetch("SELECT * FROM Bet WHERE match_id = :match_id", $data, 'Bet')) {
            foreach ($bets as $bet) $bet->calculateTotal();
            return $bets;
        } else
            return false;
    }

    function calculateTotal() {
        $this->points_total =
            $this->points_simple +
            $this->points_goals +
            $this->points_yellowcards +
            $this->points_redcards +
            $this->points_defenses;
    }

    /**
     * Create a new match and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        $data = [
            'user_id' => $this->user_id,
            'match_id' => $this->match_id,
            'type' => $this->type,
            'team' => $this->team,
            'points_simple' => null,
            'points_goals' => null,
            'points_yellowcards' => null,
            'points_redcards' => null,
            'points_defenses' => null
        ];

        if ($this->type === 1) {
            $data['points_simple'] = $this->points_simple;
        } elseif ($this->type === 2) {
            $data['points_goals'] = $this->points_goals;
            $data['points_yellowcards'] = $this->points_yellowcards;
            $data['points_redcards'] = $this->points_redcards;
            $data['points_defenses'] = $this->points_defenses;
        }
        $bet_total = $this->points_simple
            + $this->points_goals
            + $this->points_yellowcards
            + $this->points_redcards
            + $this->points_defenses;

        $user = User::Get($this->user_id);

        if ($bet_total <= $user->points) {
            if ($bet_id = $db->modify("INSERT INTO Bet (user_id, match_id, type, team, points_simple, points_goals, points_yellowcards, points_redcards, points_defenses)
                                       VALUES (:user_id, :match_id, :type, :team, :points_simple, :points_goals, :points_yellowcards, :points_redcards, :points_defenses)", $data)) {
                $user->givePoints(-$bet_total);
                if ($user->points == 0) $user->bailout();

                return $bet_id;
            } else
                return false;
        } else
            return false;
    }

    /**
     * Set the bet result to won or lost
     */
    function setResult($result) {
        $db = new DB();

        $user = User::Get($this->user_id);
        $user->givePoints($this->points_total);

        $data = [
            'bet_id' => $this->id,
            'result' => $result
        ];

        $db->modify("UPDATE Bet SET result = :result WHERE id = :bet_id", $data);
    }
}