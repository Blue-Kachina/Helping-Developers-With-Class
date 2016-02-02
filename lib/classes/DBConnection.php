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

    public function ReturnColumnData(){
        $columnData=array();
        $query=<<<QUERY_TEXT
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = ?
  AND table_schema = ?
QUERY_TEXT;


        $this->mysqli= new mysqli($this->address,$this->username,$this->password,$this->database);
        $stmt = $this->mysqli->stmt_init();
        $myTable = $this->table;
        $myDatabase = $this->database;


//echo $query;
        if($stmt->prepare($query)){
            $stmt->bind_param('ss',$myTable,$myDatabase);
//echo !$stmt ? 'failed to bind' : '';
            $stmt->execute();
//echo !$stmt ? 'failed to execute' : '';
            $stmt->bind_result($field,$type,$null,$default);
//echo !$stmt ? 'failed to bind result' : '';
            while ($stmt->fetch()){
                $columnData[] .=array("Field"=>$field,"Type"=>$type,"Null"=>$null,"Default"=>$default);
            }
            $stmt->close();
        }
        return $columnData;
    }

    public function returnTableNameOptions()
    {
        $tableList="";
        $attempt = $this->AttemptConnection();
        if (!$attempt)
            return false;

        $query =
            'SELECT DISTINCT TABLE_NAME' . PHP_EOL .
            'FROM INFORMATION_SCHEMA.COLUMNS';

        $res = mysqli_query($attempt, $query);
        while ($row = mysqli_fetch_array($res)) {
            $tableList .=
<<<TABLELIST
<option value ="$row[0]">$row[0]</option>
TABLELIST;
        }
        return $tableList;
    }

}