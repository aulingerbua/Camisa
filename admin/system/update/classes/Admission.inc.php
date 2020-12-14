<?php
/** This class handles the admission to the restricted parts of the website.
 * 
 * It contains classes for logging in and out, for checking if a user is loggend in and which permissions he has.
 * @author Armin Aulinger
 *
 */
class Admission {
	use Messages;
	/**
	 * Checks if user exists.
	 *
	 * If so, the user data are returned or NULL otherwise.
	 *
	 * @param string $uid
	 *        	the user ID to be checked
	 * @return array|NULL the user data
	 */
	private function userExists($uid) {
		if (empty ( (new Users ())->retrieve ( $uid ) ))
			return FALSE;
		else
			return TRUE;
	}
	/**
	 * Checks the password of a user.
	 *
	 * @param string $uid
	 *        	user ID
	 * @param string $pw
	 *        	password
	 * @return boolean true on success
	 */
	private function checkPwd($uid, $pw) {
		$db = db_init ();
		$id = $db->real_escape_string ( $uid );
		$qry = "SELECT pwd FROM members WHERE uid = '$id'";
		if ($row = execute_query ( $qry ))
			return password_verify ( $pw, $row [0] ['pwd'] );
		else
			return FALSE;
	}
	/**
	 * Gets the access level of a user.
	 *
	 * @param string $uid        	
	 * @return int
	 */
	private function getLevel($uid) {
		$db = db_init ();
		$id = $db->real_escape_string ( $uid );
		$qry = "SELECT level FROM roles JOIN members ON (members.role = roles.role) WHERE members.uid = '$id';";
		$row = execute_query ( $qry );
		
		return $row [0] ['level'];
	}
	/**
	 * Checks if access can be granted and writes the user ID, role and security key into $_SESSION.
	 *
	 * @param string $uid
	 *        	user ID
	 * @param string $pw
	 *        	password
	 * @return boolean true on success
	 */
	public function grantAccess($uid, $pw) {
		// first, check if the session has been started
		if (session_status () !== PHP_SESSION_ACTIVE) {
			die ( "Fehler in grant access! Keine aktive Session" );
		}
		
		if (! self::userExists ( $uid )) {
			$this->err_msg = "Benutzername existiert nicht.";
			return FALSE;
		} else if (self::checkPwd ( $uid, $pw )) {
			$level = self::getLevel ( $uid );
			$_SESSION ['user'] = $uid;
			$_SESSION ['level'] = $level;
			$_SESSION ['grant'] = sslK ();
			return TRUE;
		} else {
			$this->err_msg = "Falsches Passwort.<br><a href='" . URI . "/admin/forgotten.php'>vergessen?</a>";
			return FALSE;
		}
	}
	/**
	 * Destroys the session completely at logout
	 *
	 * @return boolean true on success
	 */
	public static function logout() {
		// delete session variables
		$_SESSION = array ();
		
		// delete session coockie
		if (ini_get ( "session.use_cookies" )) {
			$params = session_get_cookie_params ();
			setcookie ( session_name (), '', time () - 3600, $params ["path"], $params ["domain"], $params ["secure"], $params ["httponly"] );
		}
		
		session_destroy ();
		return TRUE;
	}
	/**
	 * Checks if a user is logged in and has the access level specified in the function argument.
	 *
	 * @param number $level
	 *        	the required user level, defaults to 10.
	 * @return boolean true if access is granted.
	 */
	public static function checkUserAccess($level = 10) {
		if (! $_SESSION ["grant"])
			return false;
		if (! ($_SESSION ["grant"] === sslK () && $_SESSION ['level'] <= $level))
			return false;
		else
			return true;
	}
	/**
	 * Function to reset a password.
	 * The password is only resetted if the userid and email is found un the members data base. Otherwise, false is returned and an error message is set that can be
	 * received with the method error():
	 *
	 * @param unknown $uid
	 *        	user id.
	 * @param unknown $email
	 *        	valid email address.
	 * @param unknown $newp
	 *        	new Password.
	 * @return boolean true on success, false on failure.
	 */
	public function resetPwd($uid, $email, $newp) {
		$user = new Users();
		if (! $user->retrieve ( [ 
				'uid' => $uid,
				'email' => $email 
		], NULL, 1 )) {
			$this->err_msg = "User ID oder Email konnten nicht gefunden werden.";
			return FALSE;
		}
		
		$pwd = password_hash ( $newp, PASSWORD_DEFAULT );
		if (! $user->update ( [ 
				'pwd' => $pwd 
		], $uid )) {
			$this->err_msg = "Fehler. Bitte wenden Sie sich an den Administrator der Seite.";
			return FALSE;
		}
		return TRUE;
	}
}

?>
