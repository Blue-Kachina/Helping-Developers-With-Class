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
}


//create a new server connection
$application = new Server_Class($_POST['serverType'],$_POST['serverAddress'],$_POST['serverUsername'],$_POST['serverPassword'],$_POST['serverDatabase']);


$row_count = 0;
$return_array = array();


switch ($_POST['action']) {
    case 'table':

        $success=false;
        $msg=array();
        $tableList="";

        $link = $application->AttemptConnection();

        if (!$link) {
            $msg[]= "Error: Unable to connect to MySQL." . PHP_EOL;
            $msg[]="Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            $msg[]="Debugging error: " . mysqli_connect_error() . PHP_EOL;
        } else {
            $success=true;
            $res = mysqli_query($link,"SHOW TABLES");

            $tableList='<select class="form-control">'  . PHP_EOL;
            while($cRow = mysqli_fetch_array($res))
            {
                $tableList .='<option value ="' . $cRow[0] . '">' . $cRow[0] . '</option>'  . PHP_EOL;
            }
            $tableList .='</select>'  . PHP_EOL;
            mysqli_close($link);
        }
        //return a JSON encoded array
        echo json_encode(array(
                "html" => $tableList,
                "success" => $success,
                "message" => $msg
            )
        );
        break;
}

?>