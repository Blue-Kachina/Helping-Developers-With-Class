<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ron Craig
 * Date: 8/2/12
 * Time: 7:13 AM
 */
class Msg {
	const GOOD = 1;
	const WARNING = 2;
	const ERROR = 3;
	const GENERIC = 4;

	public $critical_page_state = 0;

	public static $messages = array();

	public static $goodBox = '
			<div class="alert alert-success alert-bold-border fade in alert-dismissable">
			  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{MESSAGE}
			</div>
	';

	public static $warningBox = '
			<div class="alert alert-warning alert-bold-border fade in alert-dismissable">
			  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{MESSAGE}
			</div>
	';

	public static $errorBox = '
			<div class="alert alert-danger alert-bold-border fade in alert-dismissable">
			  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{MESSAGE}
			</div>
	';

	public static $genericBox = '
			<div class="alert alert-info alert-bold-border fade in alert-dismissable">
			  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{MESSAGE}
			</div>
	';

	public function __construct() {
		if(!isset($_SESSION['MESSAGES'])) {
			$_SESSION['MESSAGES'] = array();
		} else {
			Msg::$messages = $_SESSION['MESSAGES'];
		}
	}

	public function __destruct() {
		$_SESSION['MESSAGES'] = Msg::$messages;
	}


	public function addMessage($message, $type=Msg::GENERIC) {
		if(!is_numeric($type)) $type = Msg::GENERIC;
		Msg::$messages[$type][] = $message;
	}

	public function display($sort=true) {
		if($this->hasMessages()) {
			echo '<br/>';
    	if($this->hasMessagesOfType(Msg::ERROR)) {
				if($sort) {
					sort(Msg::$messages[Msg::ERROR]);
				}

				$output = $this->blockOutput(Msg::$messages[Msg::ERROR]);
				echo str_replace('{MESSAGE}', $output, Msg::$errorBox).'<br/>';
				Msg::$messages[Msg::ERROR] = array();
      }

			if($this->hasMessagesOfType(Msg::GOOD)) {
				if($sort) {
					sort(Msg::$messages[Msg::GOOD]);
				}

				$output = $this->blockOutput(Msg::$messages[Msg::GOOD]);
				echo str_replace('{MESSAGE}', $output, Msg::$goodBox).'<br/>';
				Msg::$messages[Msg::GOOD] = array();
			}


			if($this->hasMessagesOfType(Msg::WARNING)) {
				if($sort) {
					sort(Msg::$messages[Msg::WARNING]);
				}

				$output = $this->blockOutput(Msg::$messages[Msg::WARNING]);
				echo str_replace('{MESSAGE}', $output, MSG::$warningBox).'<br/>';
				Msg::$messages[Msg::WARNING] = array();
			}

			if($this->hasMessagesOfType(Msg::GENERIC)) {
				if($sort) {
					sort(Msg::$messages[Msg::GENERIC]);
				}

				$output = $this->blockOutput(Msg::$messages[Msg::GENERIC]);
				echo str_replace('{MESSAGE}', $output, Msg::$genericBox).'<br/>';
				Msg::$messages[Msg::GENERIC] = array();
			}

		}
	}

	private function blockOutput($messages) {
		$output='<ul class="list-unstyled">';
		foreach($messages as $message) {
			$output.='<li>'.$message.'</li>'."\n";
		}
		$output.='</ul>';
		return $output;
	}

	public function hasMessages() {
		return is_array(Msg::$messages) && count(Msg::$messages);
	}

	public function hasMessagesOfType($type) {
		return isset(Msg::$messages[$type]) && count(Msg::$messages[$type]);
	}

}

?>