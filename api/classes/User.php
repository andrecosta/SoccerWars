<?php
require 'DB.php';

/**
 * @SWG\Definition(definition="user")
 */
class User {
    public $id;
    public $email;
    public $name;
    public $avatar;
    public $points;
    public $status;

    private $pw_hash;

    /**
     * Check login credentials and get authenticated User instance, if successful
     * @param string $email
     * @param string $password
     * @return User|bool
     */
    static function Login($email, $password) {
        $db = new DB();

        $data = array(
            "email" => $email,
            "pw_hash" => sha1($password)
        );

        if ($user = $db->fetch("SELECT * FROM User WHERE email = :email AND pw_hash = :pw_hash", $data, 'User'))
            return $user;
        else
            return false;
    }

    /**
     * Get a User instance from an ID
     * @param int $id
     * @return User|bool
     */
    static function Get($id) {
        $db = new DB();

        $data = array(
            "id" => $id
        );

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
     * Creates a new User and returns its ID
     * @return int|bool
     */
    function Create() {
        $db = new DB();

        $data = array(
            "email" => $this->email,
            "pw_hash" => $this->pw_hash,
            "name" => $this->name,
            "avatar" => $this->avatar
        );

        if ($user_id = $db->modify("INSERT INTO User (email, pw_hash, name, avatar)
                                    VALUES (:email, :pw_hash, :name, :avatar)", $data))
            return $user_id;
        else
            return false;
    }

    /**
     * Creates a new User and returns its ID
     * @return int|bool
     */
    function Update() {
        $db = new DB();

        $data = array(
            "email" => $this->email,
            "pw_hash" => $this->pw_hash,
            "name" => $this->name,
            "avatar" => $this->avatar
        );

        if ($user_id = $db->modify("INSERT INTO User (email, pw_hash, name, avatar)
                                    VALUES (:email, :pw_hash, :name, :avatar)", $data))
            return $user_id;
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


    function createRandomPassword($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, $length);

        $this->pw_hash = sha1($password);

        return $password;
    }

    function setStatus($status) {
        $this->status = $status;
        $db = new DB();
        $data = array(
            "id" => $this->id,
            "status" => $this->status
        );
        if ($db->modify("UPDATE User SET status = :status WHERE id = :id", $data))
            return true;
        else
            return false;
    }
}