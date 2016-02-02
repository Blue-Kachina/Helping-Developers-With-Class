<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2016-01-29
 * Time: 10:41 PM
 */

Class DB_Connection {
    var $type;
    var $address;
    var $username;
    var $password;
    var $database;

    function __construct($serverType,$serverAddress,$serverUsername,$serverPassword,$serverDatabase){
        $this->type=$serverType;
        $this->address=$serverAddress;
        $this->username=$serverUsername;
        $this->password=$serverPassword;
        $this->database=$serverDatabase;
    }

    function AttemptConnection(){
        $attempt = mysqli_connect($this->address,$this->username,$this->password,$this->database);
        return $attempt;
    }

}