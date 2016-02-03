<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2016-01-29
 * Time: 10:41 PM
 */

Class DB_Connection {
    private $type;
    private $address;
    private $username;
    private $password;
    private $database;
    public $table;

    private $lastErrorMessage;
    private $attempt;
    private $mysqli;
    private $result;

    function __construct($serverType,$serverAddress,$serverUsername,$serverPassword,$serverDatabase){
        $this->type=$serverType;
        $this->address=$serverAddress;
        $this->username=$serverUsername;
        $this->password=$serverPassword;
        $this->database=$serverDatabase;
    }

    function __destruct(){
        if(isset($this->attempt))
        mysqli_close($this->attempt);
    }


    public function AttemptConnection(){
        $this->attempt = mysqli_connect($this->address,$this->username,$this->password,$this->database);

        if(!$this->attempt){
            $this->lastErrorMessage=
            "Error: Unable to connect to MySQL." . PHP_EOL .
            "Debugging errno: " . mysqli_connect_errno() . PHP_EOL .
            "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        }
        return $this->attempt;
    }

    public function GetLastErrorMessage(){
        return $this->lastErrorMessage;
    }


    public function ReturnCustomQueryResults($query){
        $this->result=mysqli_query($this->attempt, $query);
        $this->lastErrorMessage=mysqli_error($this->attempt);
        return $this->result;
    }

    public function ReturnColumnData()
    {
        $columnData = array();

        $this->AttemptConnection();
        if (!$this->attempt)
            return false;

        $query = "SHOW COLUMNS IN " . filter_var($this->table, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($result = mysqli_query($this->attempt, $query)) {
            return mysqli_fetch_all($result,MYSQLI_ASSOC);

        }else return false;
    }




    public function returnTableNameOptions()
    {
        $tableList="";
        $this->attempt = $this->AttemptConnection();
        if (!$this->attempt)
            return false;

        $query = "SHOW TABLES";
        /*$query =
            'SELECT DISTINCT TABLE_NAME' . PHP_EOL .
            'FROM INFORMATION_SCHEMA.COLUMNS' . PHP_EOL ;
        */

        $res = mysqli_query($this->attempt, $query);
        //printf("Error: %s\n", mysqli_error($res));

        while ($row = mysqli_fetch_array($res)) {
            $tableList .=
<<<TABLELIST
<option value ="$row[0]">$row[0]</option>
TABLELIST;
        }
        return $tableList;
    }

    /*Not Yet Implemented
    public function sanitizeMe($stringToSanitize){
        $inputType=gettype($stringToSanitize);
        switch($inputType){
            case 'NULL':

            case 'boolean':
            case 'boolean':
            case 'boolean':
            case 'boolean':
            case 'boolean':
            case 'boolean':
            case 'boolean':
            case 'boolean':
                break;
        }

    }
    */

}