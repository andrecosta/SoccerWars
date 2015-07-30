<?php

class Token {

    /**
     * Get a token from a user ID
     * @param int $user_id
     * @return string|bool
     */
    static function Get($user_id) {
        $db = new DB();

        $data = ["user_id" => $user_id];

        if ($token = $db->fetch("SELECT token FROM Token WHERE user_id = :user_id", $data))
            return $token;
        else
            return false;
    }

    /**
     * Refresh a token or create a new on if it doesn't exist
     * @param int $user_id
     * @return string|bool
     */
    static function Update($user_id) {
        $db = new DB();

        $token = uniqid(mt_rand(), true);

        $data = [
            "token" => $token,
            "user_id" => $user_id
        ];

        if ($db->modify("INSERT INTO Token (token, user_id) VALUES (:token, :user_id)
                         ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP()", $data))
            return $token;
        else
            return false;
    }

    /**
     * Validate token date
     * @param string $token
     * @return bool
     */
    static function Validate($token) {
        $db = new DB();

        $data = ['token' => $token];

        if ($db->fetch("SELECT * FROM Token WHERE token = :token
                        AND created_at < DATE_ADD(NOW(), INTERVAL 7 DAY)", $data))
            return true;
        else
            return false;
    }
}