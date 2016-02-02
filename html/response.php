<?php
/**
 * Created by PhpStorm.
 * User: Matt Leering
 * Date: 2016-01-30
 * Time: 12:04 AM
 */

require_once(__DIR__ . '/../lib/classes/DBConnection.php');
require_once(__DIR__ . '/../lib/classes/ClassTemplate.php');

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
$connection = new DB_Connection($_POST['serverType'],$_POST['serverAddress'],$_POST['serverUsername'],$_POST['serverPassword'],$_POST['serverDatabase']);

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
        } else {
        //Connection was successful.  Start building HTML that will replace a currently empty div
            $success=true;
            $res = mysqli_query($link,"SHOW TABLES");
            $tableList='<select id="selectedTable" class="form-control">'  . PHP_EOL;
            while($row = mysqli_fetch_array($res))
            {
                //$tableList .='<option value ="' . $row[0] . '">' . $row[0] . '</option>'  . PHP_EOL;
               $tableList .=<<<TABLELIST
                        <option value ="$row[0]">$row[0]</option>
TABLELIST;
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

            $query = "SHOW COLUMNS IN " . $tableName;
            if ($result = mysqli_query($link,$query)) {;
                for ($set = array (); $row = mysqli_fetch_array($result,MYSQLI_ASSOC); $set[] = $row);
                {

                }
                $template = new ClassTemplate($tableName,$set);
                $class_members = $template->GetDeclaration_Members();
                $class_load =  $template->GetDeclaration_Load();
                $class_whole = $template->GetDeclaration_WholeClass();
            }

                //Free the result set
                $result->free();

            //Close connection
            mysqli_close($link);
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