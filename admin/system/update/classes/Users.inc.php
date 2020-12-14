<?php
class Users extends Registry {
	function __construct() {
		parent::setTable ( 'members' );
		parent::setUniqueField ( 'uid' );
	}
	/**
	 * Checks how many administrators exist in the members table.
	 *
	 * @return number number of admins found. 0 has the same meaning as false.
	 */
	public function existAdmin() {
		global $db;
		
		$qry = "SELECT uid FROM members WHERE role = 'administrator'";
		
		$found = 0;
		
		if ($result = $db->query ( $qry )) {
			while ( $row = $result->fetch_row () ) {
				$found ++;
			}
		} else {
			echo "Fehler in Abfrage! " . $db->error;
		}
		
		return $found;
	}
	/**
	 * Returns an array containing the role names and their levels.
	 *
	 * @return array
	 */
	public function roList() {
		$qry = 'SELECT role,level FROM roles';
		$roles = self::executeQuery ( $qry );
		
		return $roles;
	}
	/**
	 * Returns the role name of a user
	 *
	 * @param string $user
	 *        	the user name.
	 * @return string role name.
	 */
	public function getRole($user) {
		return self::receive ( $user, NULL, "role" ) [0] ['role'];
	}
	/**
	 * Returns the level of a user.
	 *
	 * @param unknown $user
	 *        	user id
	 * @return int the user level.
	 *
	 */
	public static function getLevel($user) {
		if (! $user)
			return NULL;
		$db = db_init ();
		$us = $db->real_escape_string ( $user );
		$qry = "SELECT level FROM roles JOIN members ON (roles.role = members.role) WHERE uid = '$us'";
		if ($data = execute_query ( $qry ) [0] ['level'])
			return intval ( $data );
		else
			return NULL;
	}
	/**
	 * Displays the default form for this class
	 *
	 * @param unknown $iniValues        	
	 */
	public function showForm($iniValues = NULL) {
		// disable id input if user name exists already
		$ro = (isset ( $iniValues ['uid'] )) ? " readonly" : NULL;
		$roles = self::roList ();
		?>
<form action="" class="editor" method="post">
	<label for="uid">User ID</label> <input type="text" id="uid" name="uid"
		value="<?=$iniValues ['uid']?>" <?=$ro?> required> <label for="pwd">Passwort</label>
	<input type="text" id="pwd" name="pwd" value=""> <label for="email">Email</label>
	<input type="email" id="email" name="email"
		value="<?=$iniValues ['email']?>" required> <label for="role">Role</label>
	<select id="role" name="role">
		<?php
		
		foreach ( $roles as $rl ) {
			$role = '<option value="' . $rl ['role'] . '" ';
			if ($iniValues ['role'] === $rl ['role']) {
				$role .= 'selected';
			}
			$role .= '>' . $rl ['role'] . '</option>';
			echo $role;
		}
		?>
		</select>
		<?php
		if ($iniValues) {
			echo self::makeUpdateButton ();
			echo self::makeDeleteButton ();
			echo '<input type="hidden" id="uid" name="uid" value="' . $iniValues ['uid'] . '">';
		} else
			echo self::makeInsertButton ();
		?>
		</form>
<?php
	}
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see Registry::insert()
	 */
	public function insert(array $input) {
		// create a new user
		if ($input ['pwd'] = password_hash ( $input ['pwd'], PASSWORD_DEFAULT )) {
			Registry::insert ( $input );
			return TRUE;
		} else {
			die ( "Passwort hash schiefgegangen!" );
		}
	}
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see Registry::update()
	 */
	public function update(array $input, $which) {
		$log = start_system_log ( "Users::update" );
		if ($pwd = $input ['pwd']) {
			if (empty ( $pwd )) {
				unset ( $input ['pwd'] );
			} else {
				if (! $input ['pwd'] = password_hash ( $pwd, PASSWORD_DEFAULT )) {
					$log->write ( "Password hash schief gegangen.", LOG_ERROR );
					return FALSE;
				}
			}
		}
		return Registry::update ( $input, $which );
	}
}
?>
