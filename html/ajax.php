<?php
/**
 * Created by PhpStorm.
 * User: Matt Leering
 * Date: 2016-01-30
 * Time: 12:04 AM
 */

require_once('../lib/classes/DBConnection.php');
require_once('../lib/classes/ClassTemplate.php');

if (
    !isset($_POST['serverType'])
    || !isset($_POST['serverAddress'])
    || !isset($_POST['serverUsername'])
    || !isset($_POST['serverPassword'])
    || !isset($_POST['serverDatabase'])
){

    header('HTTP/1.1 500 Insufficient Parameters Passed');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
            "data" => "",
            "message" => "Insufficient Parameters Passed"
        )
    );
    exit();
}

//create a new database connection
$connection = new DB_Connection($_POST['serverType'], $_POST['serverAddress'], $_POST['serverUsername'], $_POST['serverPassword'], $_POST['serverDatabase']);

$success = false;
$msg = "";

$link = $connection->AttemptConnection();
//$errorToLog = print_r($connection->lastError, true) ;

//file_put_contents('c:/temp/phplog.txt',$connection->GetLastErrorMessage());

//Ensure that any failed connection attempts get reported to the user
if (!$link || !empty($connection->GetLastErrorMessage())) {
    header('HTTP/1.1 500 Connection Failed: ' . $connection->GetLastErrorMessage());
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array("success" => $success,'message' => 'Database connection attempt failed. ' . $connection->GetLastErrorMessage(), 'code' => 1337)));
    exit();
}


switch ($_POST['action']) {
    //During the action of switching to the table tab we will be looking up a list of tables, and populating a select control with them
    case 'table':
        $tableList = "";

        //Connection was successful.  Start building HTML that will replace a currently empty div
        $success = true;

        $options = $connection->returnTableNameOptions();
        $success = $options && !empty($options);

        if(!$success){
            header('HTTP/1.1 500 Internal Server Booboo - Failed to retrieve any table data');
            header('Content-Type: application/json; charset=UTF-8');
            json_encode(array("success" => $success,'message' => 'Failed to retrieve table data', 'code' => 1337));
            break;
        }

        //file_put_contents('c:/temp/phplog.txt',$options && !empty($options));

        $tableList =
            '<select id="selectedTable" class="form-control">' . PHP_EOL .
            $options . PHP_EOL .
            '</select>' . PHP_EOL;

        //return a JSON encoded array that contains detailed information about what just took place in this AJAX call
        header('Content-Type: application/json');
        echo json_encode(array(
                "html" => $tableList,
                "success" => $success,
                "message" => $msg
            )
        );
        break;

    case 'class':
        //During the action of switching to the class tab we will be looking up a list of columns in the selected table, and retrieving the metadata necessary for generating class declarations that can be used to create objects modeled after them.

        $tableName = '';
        $class_whole = '';
        $class_members = '';
        $class_load = '';
        $class_save = '';
        $row = array();
        $serverType = isset($_POST['serverType']) ? $_POST['serverType'] : '';

        //Make sure that a table name was passed in
        if (isset($_POST['serverTableName'])) {
            $tableName = $_POST['serverTableName'];
            $connection->table=$tableName;
        } else {
            $msg = "No table was selected." . PHP_EOL;
            header('HTTP/1.1 500 Internal Server Booboo');
            header('Content-Type: application/json; charset=UTF-8');
            json_encode(array("success" => $success,'message' => $msg, 'code' => 1337));
        }

        $success = true;

        $result = $connection->ReturnColumnData();
        //var_dump($result);
            if ($result) {
                $template = new ClassTemplate($tableName, $result, $serverType);
                $template->SetAllColumns($result);
                $class_members = $template->GetDeclaration_Members();
                $class_load = $template->GetDeclaration_Load();
                $class_save = $template->GetDeclaration_Save();
                $class_whole = $template->GetDeclaration_WholeClass();
            }
        else{
            $msg .= $connection->GetLastErrorMessage();
        }

        //return a JSON encoded array
        header('Content-Type: application/json');
        echo json_encode(array(
                "whole" => $class_whole,
                "members" => $class_members,
                "load" => $class_load,
                "save" => $class_save,
                "success" => $success,
                "message" => $msg
            )
        );
        break;
}
