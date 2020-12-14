<?php
class Password extends Registry {
	function __construct() {
		parent::setTable ( 'members' );
		parent::setUniqueField ( 'uid' );
	}
	// methods for setting and changing passwords
	public function showForm($iniValues = NULL) {
		// displays the defaulf form for this class
		$iniValues = NULL;
		$form [] = '<form action="" class="editor" method="post" autocomplete="off">';
		$form [] = '<div class="box-inside-form">';
		$form [] = '<div>';
		$form [] = '<label for="opw">altes Passwort</label>';
		$form [] = '<input type="password" id="opw" name="opw">';
		$form [] = '<label for="npw">neues Passwort</label>';
		$form [] = '<input type="password" id="npw" name="npw">';
		$form [] = '<label for="npwr">neues Passwort wiederholen</label>';
		$form [] = '<input type="password" id="npwr" name="npwr">';
		$form [] = '</div>';
		$form [] = '</div>';
		$form [] = '<input type="hidden" name="uid" value="' . current_user () . '">';
		$form [] = Registry::makeUpdateButton ();
		$form [] = Registry::makeQuitButton ();
		$form [] = '</form>';
		
		echo implode ( "\n", $form );
	}
	public function update(array $input, $which) {
		// change existing password
		$udata = self::retrieve ( $which ) [0];
		
		if (! $input ['npw'])
			return FALSE;
		
		if (TRUE) { //password_verify ( $new ["opw"], $udata ["pwd"] )) {
			

			if ($input ["npw"] == $input ["npwr"]) {
				if ($pwd = password_hash ( $input ['npw'], PASSWORD_DEFAULT )) {
					parent::update ( [ 
							'pwd' => $pwd 
					], $udata ["uid"] );
					return TRUE;
				} else {
					$this->err_msg = "Passwort hash schiefgegangen!";
					return FALSE;
				}
			} else {
				$this->err_msg = "Passwortwiederholung falsch!";
				return FALSE;
			}
		} else {
			$this->err_msg = "Passwort falsch!";
			return FALSE;
		}
	}
}
?>
