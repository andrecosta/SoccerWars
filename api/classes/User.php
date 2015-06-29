<?php
require 'DB.php';

class User {
    public $id;
    public $email;
    public $userName;
    public $firstName;
    public $lastName;
    public $avatar;
    public $points;
    public $status;

    /**
     * Check login credentials and get authenticated User instance, if successful
     * @param string $email
     * @param string $pw_hash
     * @return User|bool
     */
    static function Login($email, $pw_hash) {
        $db = new DB();
        $data = array(
            "email" => $email,
            "pw_hash" => $pw_hash
        );

        if ($user = $db->fetch("SELECT * FROM User WHERE email = :email AND pw_hash = :pw_hash", $data)) {
            return $user;
        } else {
            return false;
        }
    }

    /**
     * Get a User instance from an ID
     * @param int $id
     * @return User|bool
     */
    static function Get($id) {
        $db = new DB();
        $data = array("id" => $id);

        if ($user = $db->fetch("SELECT * FROM User WHERE id = :id", $data, 'User'))
            return $user;
        else
            return false;
    }

    /**
     * Get all Users
     * @return User[]|bool
     */
    static function GetAll() {
        $db = new DB();

        if ($users = $db->fetch("SELECT * FROM User", null, 'User'))
            return $users;
        else
            return false;
    }

    /**
     * Get the User instance properties as an array
     * @return array
     */
    function toArray() {
        return get_object_vars($this);
    }

    /**
     * Get the User instance as a JSON encoded string
     * @return string
     */
    function toJson() {
        return json_encode($this->toArray($this));
    }
}