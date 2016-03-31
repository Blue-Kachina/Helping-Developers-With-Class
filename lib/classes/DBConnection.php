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
        //if(isset($this->connection))
        //mysqli_close($this->connection);
    }



    //Attempt Connection
    public function AttemptConnection()
    {
        if (empty($this->type)) {
            return false;
        }

        //MySQL
        elseif ($this->type == "MySQL") {
            $this->connection = mysqli_connect($this->address, $this->username, $this->password, $this->database);

            if (!$this->connection) {
                $this->lastErrorMessage =
                    "Error: Unable to connect to MySQL." . PHP_EOL .
                    "Debugging errno: " . mysqli_connect_errno() . PHP_EOL .
                    "Debugging error: " . mysqli_connect_error() . PHP_EOL;
                return false;
            }

        //SQL Server
        } elseif ($this->type == "MSSQL") {
            $connectionInfo = array( "Database"=>$this->database, "UID"=>$this->username, "PWD"=>$this->password);
            $this->connection = sqlsrv_connect($this->address, $connectionInfo);
            if (!$this->connection) {
                $this->lastErrorMessage =
                    "Error: Unable to connect to SQL Server." . PHP_EOL .
                    "Debugging error: " . sqlsrv_errors() . PHP_EOL;
                return false;
            }
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


    //Gets a list of the tables in the selected database.  Returns them as options for within an HTML select element
    public function returnTableNameOptions()
    {

        $tableList = "";
        $allTables = array();

        if(empty($this->type)){
            return false;
        }
        elseif($this->type=="MySQL"){
            $allTables = $this->ReturnTableNames_MySQL();
        }
        elseif($this->type="MSSQL"){
            $allTables = $this->ReturnTableNames_SQL_Server();
        }

        foreach ($allTables as $table) {
            $tableList .= "<option value =\"$table\">$table</option>";
        }
        return $tableList;
    }



    public function ReturnColumnData()
    {
        $this->AttemptConnection();
        if (!$this->connection)
            return false;


        if($this->type == "MySQL") {
            $query =
                'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA ' . PHP_EOL .
                'FROM INFORMATION_SCHEMA.COLUMNS ' . PHP_EOL .
                'WHERE TABLE_SCHEMA = \'' . filter_var($this->database, FILTER_SANITIZE_STRING) . '\' AND ' . PHP_EOL .
                'TABLE_NAME = \'' . filter_var($this->table, FILTER_SANITIZE_STRING) . '\'';

            if ($result = mysqli_query($this->connection, $query)) {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);

            } else return false;
        }
        elseif($this->type == "MSSQL")
        {
            $tableName = filter_var($this->table, FILTER_SANITIZE_STRING);
            $columnList=array();
            $query=<<<FETCH_COLUMNS
                SELECT
                    c.name 'COLUMN_NAME',
                    t.Name 'DATA_TYPE',
                    c.is_nullable 'IS_NULLABLE',
                    ISNULL(i.is_primary_key, 0) 'COLUMN_KEY',
                    c.max_length 'MAX_LENGTH',
                    '' 'COLUMN_DEFAULT',
                    '' 'EXTRA'
                FROM
                    sys.columns c
                INNER JOIN
                    sys.types t ON c.user_type_id = t.user_type_id
                LEFT OUTER JOIN
                    sys.index_columns ic ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                LEFT OUTER JOIN
                    sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
                WHERE
                    c.object_id = OBJECT_ID('$tableName')
FETCH_COLUMNS;
            $stmt = sqlsrv_query( $this->connection, $query );
            while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)){
                $columnList[]=$row;
            }
            return $columnList;
        }
        return false;
    }




    private function ReturnTableNames_MySQL(){
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
        return mysqli_fetch_array($res);
    }

    private function ReturnTableNames_SQL_Server(){
        $tableList=array();
        $this->connection = $this->AttemptConnection();
        if (!$this->connection)
            return false;

        $query =<<<GET_TABLENAMES
SELECT DISTINCT TABLE_NAME
FROM {$this->database}.INFORMATION_SCHEMA.COLUMNS
ORDER BY TABLE_NAME;
GET_TABLENAMES;

        $stmt = sqlsrv_query( $this->connection, $query );
        while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC)){
            $tableList[]=$row[0];
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