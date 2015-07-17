<?php

class User {
    public $id;
    public $email;
    public $name;
    public $avatar;
    public $points;
    public $badges;
    public $status;
    public $created_at;

    private $token;
    private $pw_hash;

    /**
     * Check login credentials and return the user ID
     * @param string $email
     * @param string $password
     * @return int|bool
     */
    static function Login($email, $password) {
        $db = new DB();

        $data = [
            "email" => $email,
            "pw_hash" => sha1($password)
        ];

        if ($id = $db->fetch("SELECT id FROM User WHERE email = :email AND pw_hash = :pw_hash", $data)) {
            // Create or renew the user token upon login
            Token::Create($id);
            return $id;
        }
        else
            return false;
    }

    /**
     * Get a user instance from an ID
     * @param int $id
     * @return User|bool
     */
    static function Get($id) {
        $db = new DB();

        $data = ["id" => $id];

        if ($user = $db->fetch("SELECT * FROM User WHERE id = :id", $data, 'User')) {
            $user->token = Token::Get($id);
            $user->getAvatars();
            $user->getBadges();

            return $user;
        } else
            return false;
    }

    /**
     * Get all users
     * @return User[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($users = $db->fetch("SELECT * FROM User", null, 'User')) {
            foreach ($users as &$user) {
                $user->getAvatars();
                $user->getBadges();
            }
            return $users;
        } else
            return false;
    }

    /**
     * Get a user instance from a token
     * @param int $token
     * @return User|bool
     */
    static function GetByToken($token) {
        $db = new DB();

        $data = ["token" => $token];

        if ($user_id = $db->fetch("SELECT user_id FROM Token WHERE token = :token", $data)) {
            $user = User::Get($user_id);
            return $user;
        } else
            return false;
    }

    /**
     * Create a new user and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        // Create avatar
        $avatar = uniqid();
        $genders = ['male', 'female'];
        $gender = $genders[array_rand($genders)];
        $image = file_get_contents("http://eightbitavatar.herokuapp.com/?id=$this->email&s=$gender&size=150");
        file_put_contents("../static/avatars/${avatar}_150.jpg", $image);
        $image = file_get_contents("http://eightbitavatar.herokuapp.com/?id=$this->email&s=$gender&size=32");
        file_put_contents("../static/avatars/${avatar}_32.jpg", $image);

        $data = [
            "email" => $this->email,
            "pw_hash" => $this->pw_hash,
            "name" => $this->name,
            "avatar" => $avatar
        ];

        if ($user_id = $db->modify("INSERT INTO User (email, pw_hash, name, avatar)
                                    VALUES (:email, :pw_hash, :name, :avatar)", $data)) {
            return $user_id;
        } else
            return false;
    }

    /**
     * Get the user instance properties as an array
     * @return array
     */
    function toArray() {
        return get_object_vars($this);
    }

    /**
     * Get the user instance as a JSON encoded string
     * @return string
     */
    function toJson() {
        return json_encode($this->toArray($this));
    }


    function createRandomPassword($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, $length);

        // Temporary password while emails are not implemented
        //$this->pw_hash = sha1($password);
        $this->pw_hash = sha1("teste");

        return $password;
    }

    function setStatus($status) {
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
    }

    function getToken() {
        return $this->token;
    }

    function getAvatars() {
        $avatar = $this->avatar;
        $this->avatar = [
            'big' => STATIC_URL . "/avatars/${avatar}_150.jpg",
            'small' => STATIC_URL . "/avatars/${avatar}_32.jpg"
        ];
    }

    function getBadges() {
        $db = new DB();

        $data = ["user_id" => $this->id];

        $badges = $db->fetch("SELECT * FROM Badge");
        $user_badges = $db->fetch("SELECT id, name, points FROM UserBadge, Badge WHERE Badge.id = UserBadge.badge_id AND user_id = :user_id", $data);

        foreach ($badges as &$badge) {
            $badge['image'] = STATIC_URL . '/badges/' . $badge['id'] . '.png';

            if ($user_badges) {
                if (isset($user_badges[0])) {
                    foreach ($user_badges as &$user_badge)
                        if ($user_badge['id'] == $badge['id'])
                            $badge['unlocked'] = 1;
                } else {
                    if ($user_badges['id'] == $badge['id'])
                        $badge['unlocked'] = 1;
                }
            }
        }

        $this->badges = $badges;
    }
}