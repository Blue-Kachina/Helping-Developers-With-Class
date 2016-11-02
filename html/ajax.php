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
    || !isset($_POST['serverAddress']) || empty($_POST['serverAddress'])
    || !isset($_POST['serverUsername']) || empty($_POST['serverUsername'])
    || !isset($_POST['serverPassword']) || empty($_POST['serverPassword'])
    || !isset($_POST['serverDatabase']) || empty($_POST['serverDatabase'])
){

    header('HTTP/1.1 500 Insufficient Parameters Passed');
    header('Content-Type: application/json; charset=UTF-8');
    exit();
}

//create a new database connection
$connection = new DB_Connection($_POST['serverType'], $_POST['serverAddress'], $_POST['serverUsername'], $_POST['serverPassword'], $_POST['serverDatabase']);

$success = false;
$msg = "";

$link = $connection->AttemptConnection();
//Ensure that any failed connection attempts get reported to the user
if (!$link || !empty($connection->GetLastErrorMessage())) {
    header('HTTP/1.1 500 Connection Failed: ' . $connection->GetLastErrorMessage());
    header('Content-Type: application/json; charset=UTF-8');
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
            break;
        }

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
        $update_only = '';
        $class_load = '';
        $class_save = '';
        $row = array();
        $serverType = isset($_POST['serverType']) ? $_POST['serverType'] : '';

        //Make sure that a table name was passed in
        if (isset($_POST['serverTableName']) && !empty($_POST['serverTableName'])) {
            $tableName = $_POST['serverTableName'];
            $connection->table=$tableName;
        } else {
            header('HTTP/1.1 500 No Table Was Selected');
            header('Content-Type: application/json; charset=UTF-8');
            exit();
        }

        $success = true;

        $result = $connection->ReturnColumnData();
            if ($result) {
                $template = new ClassTemplate($tableName, $result, $serverType);
                $update_only = $template->GetDeclaration_UpdateablePart();
                //$class_load = $template->GetDeclaration_Load();
                //$class_save = $template->GetDeclaration_Save();
                $class_whole = $template->GetDeclaration_WholeClass();
            }
        else{
            $msg .= $connection->GetLastErrorMessage();
            header('HTTP/1.1 500 Error retrieving table data: ' . $msg);
            header('Content-Type: application/json; charset=UTF-8');
            exit();
        }

        //return a JSON encoded array
        header('Content-Type: application/json');
        echo json_encode(array(
                "whole" => $class_whole,
                "update_only" => $update_only,
                "success" => $success,
                "message" => $msg
            )
        );
        break;

    case 'generate_all':


        //User decided to generate all classes - so we'll create them in the "auto-generated-files" folder
        $row = array();
        $serverType = isset($_POST['serverType']) ? $_POST['serverType'] : '';

        $tableList = "";
        $allTables = array();

        $allTables = $connection->returnTableNames();
        deleteAllGeneratedFiles();
        $success = true;
        $msg="Succesfully generated files";

        foreach($allTables as $tableName){
            $connection->table=$tableName;
            $result = $connection->ReturnColumnData();
            if ($result) {
                $template = new ClassTemplate($tableName, $result, $serverType);
                $template->SetAllColumns($result);
                $class_whole = $template->GetDeclaration_WholeClass();
                file_put_contents('c:/temp/phplog.txt', $class_whole);
                $currentPath = __FILE__ ;
                $withoutAjax = substr($currentPath,0,(strlen($currentPath)-strlen('ajax.php')));
                $pathToNewFile = $withoutAjax . 'auto-generated-files\\' . $tableName .'.php';
                $bytesWritten = file_put_contents($pathToNewFile,$class_whole);

            } else {
                $msg .= $connection->GetLastErrorMessage();
                header('HTTP/1.1 500 Error retrieving table data: ' . $msg);
                header('Content-Type: application/json; charset=UTF-8');
                exit();
            }
        }

        //return a JSON encoded array
        header('Content-Type: application/json');
        echo json_encode(array(
                "success" => $success,
                "message" => $msg
            )
        );
        break;
}

function deleteAllGeneratedFiles(){
    $files = glob('auto-generated-files/*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }
}