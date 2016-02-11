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
    public $connection;
    public $result;

    function __construct($serverType,$serverAddress,$serverUsername,$serverPassword,$serverDatabase){
        $this->type=$serverType;
        $this->address=$serverAddress;
        $this->username=$serverUsername;
        $this->password=$serverPassword;
        $this->database=$serverDatabase;
    }

    function __destruct(){
        if(isset($this->connection))
        mysqli_close($this->connection);
    }

    public function AttemptConnection(){
        $this->connection = mysqli_connect($this->address,$this->username,$this->password,$this->database);

        if(!$this->connection){
            $this->lastErrorMessage=
            "Error: Unable to connect to MySQL." . PHP_EOL .
            "Debugging errno: " . mysqli_connect_errno() . PHP_EOL .
            "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        }
        return $this->connection;
    }

    public function GetLastErrorMessage(){
        return $this->lastErrorMessage;
    }

    public function ReturnCustomQueryResults($query){
        $result = mysqli_query($this->connection, $query);
        $this->result= $result;
        if (!$this->result){
            $this->lastErrorMessage=mysqli_error($this->connection);
            return false;
        }
        else{
            $leftWord = explode(' ',trim($query))[0];
            if ( $leftWord == "SELECT" || $leftWord == "SHOW" || $leftWord == "DESCRIBE" || $leftWord == "EXPLAIN" ){
                return mysqli_fetch_all($this->result, MYSQLI_ASSOC);
            }
            return true;
        }
    }

    public function ReturnColumnData()
    {

        $this->AttemptConnection();
        if (!$this->connection)
            return false;

        //$query = "SHOW COLUMNS IN " . filter_var($this->table, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $query =
            'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA ' . PHP_EOL .
            'FROM INFORMATION_SCHEMA.COLUMNS ' . PHP_EOL .
            'WHERE TABLE_SCHEMA = \'' . filter_var($this->database,FILTER_SANITIZE_STRING) . '\' AND ' .PHP_EOL .
            'TABLE_NAME = \'' . filter_var($this->table,FILTER_SANITIZE_STRING) . '\'';

        if ($result = mysqli_query($this->connection, $query)) {
            return mysqli_fetch_all($result,MYSQLI_ASSOC);

        }else return false;
    }

    public function returnTableNameOptions()
    {
        $tableList="";
        $this->connection = $this->AttemptConnection();
        if (!$this->connection)
            return false;

        //$query = "SHOW TABLES";
        $query =
            'SELECT DISTINCT TABLE_NAME' . PHP_EOL .
            'FROM INFORMATION_SCHEMA.TABLES ' . PHP_EOL .
            'WHERE TABLE_SCHEMA = \'' . $this->database . '\'';


        $res = mysqli_query($this->connection, $query);

        while ($row = mysqli_fetch_array($res)) {
            $tableList .= "<option value =\"$row[0]\">$row[0]</option>";
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