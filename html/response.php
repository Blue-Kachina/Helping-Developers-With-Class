<?php
/**
 * Created by PhpStorm.
 * User: User
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
    exit;
}


//create a new server connection
$application = new Server_Class($_POST['serverType'],$_POST['serverAddress'],$_POST['serverUsername'],$_POST['serverPassword'],$_POST['serverDatabase']);


$row_count = 0;
$return_array = array();


switch ($_POST['action']) {
    case 'table':
        //return a JSON encoded array
        echo json_encode(array(
                "data" => "",
                "message" => $application->AttemptConnection()
            )
        );
        break;
}

?>