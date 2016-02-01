<?php
/**
 * Created by PhpStorm.
 * User: Matt Leering
 * Date: 2016-01-30
 * Time: 12:04 AM
 */

require_once(__DIR__ . '/../lib/classes/server_class.php');

if(
    !isset($_POST['serverType'])
||  !isset($_POST['serverAddress'])
||  !isset($_POST['serverUsername'])
||  !isset($_POST['serverPassword'])
||  !isset($_POST['serverDatabase'])
){
    echo json_encode(array(
            "data" => "",
            "message" => "Insufficient Parameters Passed"
        )
    );
}

//create a new server connection
$connection = new Server_Class($_POST['serverType'],$_POST['serverAddress'],$_POST['serverUsername'],$_POST['serverPassword'],$_POST['serverDatabase']);

switch ($_POST['action']) {
    //During the action of switching to the table tab we will be looking up a list of tables, and populating a select control with them
    case 'table':

        $success=false;
        $msg="";
        $tableList="";

        //Invoke the server's connection attempt method
        $link = $connection->AttemptConnection();

        //Ensure that any failed connection attempts get reported to the user
        if (!$link) {
            $msg = "Error: Unable to connect to MySQL." . PHP_EOL;
            $msg .="Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            $msg .="Debugging error: " . mysqli_connect_error() . PHP_EOL;

//            header('HTTP/1.1 500 Internal Server Booboo');
//            header('Content-Type: application/json; charset=UTF-8');
//            die(json_encode(array('message' => $msg , 'code' => 1337)));
        } else {
        //Connection was successful.  Start building HTML that will replace a currently empty div
            $success=true;
            $res = mysqli_query($link,"SHOW TABLES");
            $tableList='<select id="selectedTable" class="form-control">'  . PHP_EOL;
            while($row = mysqli_fetch_array($res))
            {
                $tableList .='<option value ="' . $row[0] . '">' . $row[0] . '</option>'  . PHP_EOL;
            }
            $tableList .='</select>'  . PHP_EOL;
            mysqli_close($link);
        }
        //return a JSON encoded array that contains detailed information about what just took place in this AJAX call
        echo json_encode(array(
                "html" => $tableList,
                "success" => $success,
                "message" => $msg
            )
        );
        break;

    case 'class':
        //During the action of switching to the class tab we will be looking up a list of columns in the selected table, and retrieving the metadata necessary for generating class declarations that can be used to create objects modeled after them.
        $success=false;
        $msg='';
        $tableName='';
        $class_whole='';
        $class_members='';
        $class_load='';
        $class_save='';
        $key_field_index=array();
        $row=array();

        //Make sure that a table name was passed in
        if(isset($_POST['serverTableName'])){
            $tableName = $_POST['serverTableName'];
        }
        else{
            $msg = "No table was selected." . PHP_EOL;
        }

        //Invoke the server's connection attempt method
        $link = $connection->AttemptConnection();

        //Ensure that any failed connection attempts get reported to the user
        if (!$link) {
            $msg = "Error: Unable to connect to MySQL." . PHP_EOL;
            $msg .="Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            $msg .="Debugging error: " . mysqli_connect_error() . PHP_EOL;
        } else {
            $success=true;

            $class_whole = "<?php" . PHP_EOL;
            $class_whole  .='' . PHP_EOL ;
            $class_whole  .='require_once(\'Table.php\');' . PHP_EOL. PHP_EOL . PHP_EOL;


            $class_whole .='Class ' . $tableName . ' EXTENDS Table {'  . PHP_EOL;

            //Grab all the column names.  These will be used for public member declarations near the top of the class
            $query = "SHOW COLUMNS IN " . $tableName;
            if ($result = mysqli_query($link,$query)) {;


                $class_members  = '//' . str_repeat("\t",3) . 'Field' . str_repeat("\t",7) . 'Type' . str_repeat("\t",9) . 'Null' . str_repeat("\t",8) . 'Key' . str_repeat("\t",10) . 'Default' . str_repeat("\t",9) . 'Extra' . PHP_EOL;
                $class_members .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;

                for ($i = 0; $row[] = mysqli_fetch_assoc($result); ++$i)  //Field, Type, Null, Key, Default, Extra
                {
                    //print_r($row);
                    $myField = $row[$i]['Field'];
                    $myType = $row[$i]['Type'];
                    $myNull = $row[$i]['Null'];
                    $myKey = $row[$i]['Key'];
                    $myDefault = $row[$i]['Default'];
                    $myExtra = $row[$i]['Extra'];


                    $class_members .= '    public $' . $myField . ';'
                        . str_repeat("\t", numTabStopsToUse(15,25 + 1 + strlen($myField))) . '//' . $myType
                        . str_repeat("\t", numTabStopsToUse(10,strlen($myType)-1)) . $myNull
                        . str_repeat("\t", numTabStopsToUse(10,strlen($myNull)-1)) . $myKey
                        . str_repeat("\t", numTabStopsToUse(10,strlen($myKey)-1)) . $myDefault
                        . str_repeat("\t", numTabStopsToUse(10,strlen($myDefault)-1)) . $myExtra
                        . PHP_EOL; //10 Tab stops from the far left, 13 characters added to the field name, zero-based, each tab stop is akin to 4 characters
                    if (isset($row['Key']) && $row['Key'] == 'PRI') {
                        $key_field_index[] = $i;
                    }
                }


                //the following variable will only work properly on tables that have a single, solitary primary key
                $fieldName = $row[0]['Field'];

                $class_load =  '    public function load(param_' . $fieldName . ') {' . PHP_EOL;
                $class_load .= '        $db = get_db_connection();' . PHP_EOL;
                $class_load .= '        $sql = \'SELECT * FROM [' . $tableName .  '] WHERE [' . $fieldName . '] = ?\';' . PHP_EOL;
                $class_load .= '        $rs = $db->query($sql, null, null, array(' . $fieldName . '));' . PHP_EOL;
                $class_load .= '' . PHP_EOL;
                $class_load .= '        if($rs && $rs->rowCount() > 0) {' . PHP_EOL;
                $class_load .= '            $row = $rs->fetch(CoreDB::FETCH_ASSOC);' . PHP_EOL;
                $class_load .= '            $this->loadFromArray($row);' . PHP_EOL;
                $class_load .= '        }' . PHP_EOL;
                $class_load .= '    }';
            }

                //Free the result set
                $result->free();

            //Close connection
            mysqli_close($link);

            $class_whole .= $class_members . PHP_EOL . PHP_EOL ;
            $class_whole .= $class_load;
            $class_whole .='}'  . PHP_EOL;
        }
        //return a JSON encoded array
        echo json_encode(array(
                "whole" => $class_whole,
                "members" => $class_members,
                "load" => $class_load,
                "success" => $success,
                "message" => $msg
            )
        );
        break;
}

function numTabStopsToUse($numTabsWithoutText, $lengthOfText){
    $numCharsPerTab = 4;
    $bestGuess =  $numTabsWithoutText - $lengthOfText / $numCharsPerTab ;
    return $bestGuess > 0? $bestGuess : 0;
}