<?php
@ob_start();

include_once(__DIR__ . '/config/conn_constants.inc.php');

ini_set('default_charset', 'utf-8');
ini_set('session.gc_maxlifetime', 3*60*60); // 3 hours

mb_internal_encoding('utf-8');

@session_name(SESSION_NAME);
@session_start();

date_default_timezone_set(APP_TIMEZONE);

if(!defined('DIR_ROOT')) define('DIR_ROOT', __DIR__);

require_once(DIR_ROOT.'/lib/classes/coredb/coredb.php');
require_once(DIR_ROOT.'/lib/classes/Msg.php');

$MSG = new Msg();
$GLOBALS['MSG'] = $MSG;

$DB = null;
$GLOBALS['DB'] = $DB;


/**
 * @return Msg $msg
 */
function get_msg_system() {
    return $GLOBALS['MSG'];
}

function getPost($item, $default = '') {
	// POST wins over GET
	$val = $default;
	if(isset($_GET[$item])) $val = $_GET[$item];
	if(isset($_POST[$item])) $val = $_POST[$item];

	return $val;
}

function formatAddressBlock($orientation = 'h', $address, $city='', $province='', $postal_code='') {
	$sep = $orientation == 'v' ? '<br/>' : ', ';

	$block = array();

    if(!empty($address)) $block[] = $address;

	if(!empty($city)) $block[] = $city . (!empty($province) ? ', ' . $province : '');

	if(!empty($postal_code)) $block[] = $postal_code;

	return implode($sep, $block);
}

/**
 * @return CoreDB
 * @see CoreDB
 */
function get_db_connection() {
    global $DB;

    if(is_a($DB, 'CoreDB')) {
        return $DB;
    } else {
			try {
				$DB = new CoreDB(DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);

			} catch(CoreDBException $e) { // if we can't connect to the database this is a show stopper - bust right out of here!
				die('There was an error communicating with the product database. Please try again and if the problem persists please contact a member of your support team.');
			}
			return $DB;
		}
}

/**
 * @param mixed $var
 * @param string $title
 */
function debugOut($var='', $title='') {
    $trace = current(debug_backtrace()); // most recent referrer
    if(!empty($title)) {
        $_SESSION['DEBUG_MSG'][$title][] = array('Line'=>$trace['line'], 'File'=>$trace['file'], 'Data'=>$var);
    } else {
        $_SESSION['DEBUG_MSG'][] = array('Line'=>$trace['line'], 'File'=>$trace['file'], 'Data'=>$var);
    }
}

function isPostBack() {
    return ($_SERVER['REQUEST_METHOD'] == 'POST');
}

function detectMobile () {
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
        return true;
    }
    else {
        return false;
    }
}

/**
 * @purpose check to see if user is logged in, if not redirect user to login page
 * @author David Leonard
 */
function isLoggedIn() {
    if(!isset($_SESSION['user'])) header('Location: '.WEB_ROOT.'/signin.php');
}

class DebugCatch {

    public function __destruct() {
        ob_start();
        if(isset($_SESSION['DEBUG_MSG']) && $_SESSION['DEBUG_MSG']) {
            echo '<pre style="margin: 10px; padding: 10px; border:1px solid #000; background-color: #ffe; font-size: 10pt;clear: both; display: block;">';
            print_r($_SESSION['DEBUG_MSG']);
            unset($_SESSION['DEBUG_MSG']);
            echo '<hr/>';

            if(extension_loaded('xdebug') && xdebug_is_enabled()) {
                echo '<strong>XDebug Information</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_xdebuginfo\', this)">[Show]</a></strong><div id="dbg_xdebuginfo" style="display: none;">';
                echo '<br/>';
                echo 'Memory Usage: '. xdebug_memory_usage().'<br/>';
                echo 'Peak Memory Usage: '. xdebug_peak_memory_usage().'<br/><br/>';

                echo 'Headers:<br/>';
                var_dump(xdebug_get_headers());

                echo 'Function Stack:<br/>';
                var_dump(xdebug_get_function_stack());
                echo '</div><hr/>';
            }

            echo '<strong>POST Vars:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_postinfo\', this);">[Show]</a></strong><div id="dbg_postinfo" style="display: none;"><br/>';
            var_dump($_POST);
            echo '</div><hr/>';

            echo '<strong>GET Vars:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_getinfo\', this);">[Show]</a></strong><div id="dbg_getinfo" style="display: none;"><br/>';
            var_dump($_GET);
            echo '</div><hr/>';

            echo '<strong>SERVER Vars:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_serverinfo\', this);">[Show]</a></strong><div id="dbg_serverinfo" style="display: none;"><br/>';
            var_dump($_SERVER);
            echo '</div><hr/>';

            echo '<strong>ENV Vars:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_envinfo\', this);">[Show]</a></strong><div id="dbg_envinfo" style="display: none;"><br/>';
            var_dump($_ENV);
            echo '</div><hr/>';

            echo '<strong>COOKIES:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_cookieinfo\', this);">[Show]</a></strong><div id="dbg_cookieinfo" style="display: none;"><br/>';
            var_dump($_COOKIE);
            echo '</div><hr/>';

            echo '<strong>FILES:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_filesinfo\', this);">[Show]</a></strong><div id="dbg_filesinfo" style="display: none;"><br/>';
            var_dump($_FILES);
            echo '</div><hr/>';

            echo '<strong>SESSION:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_sessioninfo\', this);">[Show]</a></strong><div id="dbg_sessioninfo" style="display: none;"><br/>';
            var_dump($_SESSION);
            echo '</div><hr/>';

            echo '<strong>Headers List:</strong> <strong><a href="#" onclick="return toggle_block(\'dbg_headerslistinfo\', this);">[Show]</a></strong><div id="dbg_headerslistinfo" style="display: none;"><br/>';
            var_dump(headers_list());
            echo '</div><hr/>';


            echo '</pre>';
        }

        $conn = get_db_connection();
        if($conn->profiling && count($conn->profiling_results)) {
            $conn->printProfilingInfo();
        }

        $buf = ob_get_contents();
        ob_end_clean();

        if(defined('ERRORS_ON') && ERRORS_ON) {
            echo $buf;

            $fp = fopen(DIR_ROOT.'/debugOut.html', 'w');
            fwrite($fp, $buf);
            fclose($fp);
        }

    }

}

$ddgdfg = new DebugCatch();

require_once(DIR_ROOT . '/lib/classes/tables/User.php');

if(isset($_SESSION['no_access'])) {
    get_msg_system()->addMessage('Sorry, you do not have the privileges to access that area of the system.', Msg::ERROR);
    unset($_SESSION['no_access']);
}