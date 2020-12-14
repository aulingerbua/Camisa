<?php
/**
 * Handles the installation and update (todo!) of the main system and additional modules.
 * @author Armin Aulinger
 *
 */
class Install {
	private $module;
	private $version;
	private $group;
	private $src_path;
	
	/**
	 * Constructor for installing modules.
	 * If no parameters are passed, the base system is installed.
	 *
	 * @param string $module        	
	 * @param string $version        	
	 */
	function __construct($module = NULL, $version = NULL) {
		$this->src_path = $module ? ModulesPath . $module . "/" : BasePath . "admin/system/";
		$this->module = $module ? $module : "system";
		$this->version = $version ? $version : CamisaVersion;
		$this->group = $this->module;
	}
	/**
	 * Checks if the module is already installed.
	 * Does not check for the installed version!
	 *
	 * @return boolean
	 */
	public function isInstalled() {
		$db = db_init ();
		$qry = "SELECT 1 FROM system WHERE entry ='module' AND value = '$this->module';";
		$result = $db->query ( $qry );
		if (! $result)
			return FALSE;
		$found = $result->fetch_row ();
		return $found [0];
	}
	/**
	 * Installs the tables into the data base.
	 * The set up of the tables must be stored in a file "tables.json" that follows the convention.
	 *
	 * @return boolean true on success, false on failure
	 */
	public function installTables() {
		global $db;
		$log = start_install_log ( "installTables" );
		
		if (DBtype === "psql")
			$tablefile = "tables_p.json";
		else
			$tablefile = "tables.json";
		
		if (! $tables_json = file_get_contents ( $this->src_path . $tablefile ))
			return false;
		
		$tables_obj = json_decode ( $tables_json );
		
		foreach ( $tables_obj->tables as $table ) {
			// var_dump($table);
			$name = key ( $table );
			
			if (is_array ( $table->primary_key ))
				$pk = $table->primary_key ? ", PRIMARY KEY (" . implode ( ",", $table->primary_key ) . ")" : null;
			else
				$pk = $table->primary_key ? ", PRIMARY KEY (" . $table->primary_key . ")" : null;
			
			if (is_array ( $table->unique_key ))
				$uk = $table->unique_key ? ", UNIQUE (" . implode ( ",", $table->unique_key ) . ")" : null;
			else
				$uk = $table->unique_key ? ", UNIQUE (" . $table->unique_key . ")" : null;
			
			$tbl_qry = "CREATE TABLE IF NOT EXISTS " . key ( $table ) . " (";
			foreach ( $table->$name as $field ) {
				// var_dump($field);
				$default = $field->default ? "DEFAULT $field->default" : NULL;
				$null = $field->null ? NULL : "NOT NULL";
				$autoinc = $field->autoinc ? "AUTO_INCREMENT" : NULL;
				// make types PSQL compatible!!
				$field_def [] = "$field->name $field->type $null $default $autoinc";
			}
			$tbl_qry .= implode ( ",", $field_def );
			$tbl_qry .= " $pk $uk);";
			
			echo '<div class="install_message';
			if ($result = $db->query ( $tbl_qry )) {
				echo ' success">Installiere ' . $name;
			} else {
				echo ' fail">';
				echo "<p>Data base error! Could not install table $name.</p></div>";
				$log->write ( "Data base error: " . $db->error, LOG_ERROR );
				return false;
			}
			echo "</div>";
			
			unset ( $field_def );
			// fill table with default values
			if ($prefill = $table->prefill) {
				$number_of_fills = 1;
				foreach ( $prefill as $pf ) {
					$f [] = $pf->field;
					if (is_array ( $pf->value )) {
						$number_of_fills = count ( $pf->value );
						for($i = 0; $i < $number_of_fills; $i ++) {
							$v [$i] [] = $pf->value [$i];
						}
					} else
						$v [] = $pf->value;
				}
				if ($number_of_fills > 1) {
					for($i = 0; $i < $number_of_fills; $i ++)
						$prefill_qry [] = "INSERT INTO $name (" . implode ( ",", $f ) . ") VALUES (" . implode ( ",", $v [$i] ) . ");";
				} else
					$prefill_qry [] = "INSERT INTO $name (" . implode ( ",", $f ) . ") VALUES (" . implode ( ",", $v ) . ");";
				
				echo '<div class="install_message';
				for($p = 0; $p < $number_of_fills; $p ++) {
					if (! $result = $db->query ( $prefill_qry [$p] )) {
						echo ' fail">';
						echo "<p>Data base error! Could not insert default values into table $name.</p></div>";
						$log->write ( "Data base error: " . $db->error, LOG_ERROR );
						return false;
					}
				}
				echo ' success">Default values inserted.</div>';
				
				unset ( $f, $v, $prefill_qry );
			}
		}
		$log->write ( "Tables of $this->module installed", LOG_INFO );
		return true;
	}
	/**
	 * Copies the files named in the functions array from the module directory to the include directory and renames it to /modulename/.inc.php.
	 *
	 * @param array $functions
	 *        	an array of function names without suffix
	 * @return boolean true on success, false on failure
	 */
	public function installFunctions(array $functions) {
		$log = start_install_log ( "installFunctions" );
		foreach ( $functions as $function ) {
			if (copy ( $this->src_path . "$function.inc.php", IncPath . "$function.inc.php" ))
				$log->write ( "Installed functions of module" . $this->module, LOG_INFO );
			else
				return false;
		}
		return true;
	}
	public function installSystemFiles($type, $update = FALSE) {
		if (! $module)
			$directory = SystemUpdatePath;
		elseif ($update)
			$directory = $src_path . "update";
		else
			$directory = $src_path;
		$log = start_install_log ( "installSystemFiles" );
		$dirContents = array_filter ( scandir ( $directory ), function ($a) {
			return ! in_array ( $a, [ 
					".",
					".." 
			] );
		} );
		
		$grepstring = "/.*(\." . $type . "\.php)$/";
		
		$dirContents = array_filter ( $dirContents, function ($a) use ($grepstring) {
			return preg_match ( $grepstring, $a );
		} );
		
		echo "<ul>";
		foreach ( $dirContents as $d )
			echo "<li>$d</li>";
		echo "</ul>";
		
		return true;
	}
	/**
	 * Copies the files named in the AJAXscript array from the module directory to the include directory
	 *
	 * @param array $scripts
	 *        	an array of script names without suffix
	 * @return boolean true on success, false on failure
	 */
	public function installAJAXscripts(array $scripts) {
		$log = start_install_log ( "installAJAXscripts" );
		foreach ( $scripts as $script ) {
			if (copy ( $this->src_path . "$script.ajax.php", IncPath . "/ajax/$script.ajax.php" ))
				$log->write ( "Installed functions of module" . $this->module, LOG_INFO );
			else
				return false;
		}
		return true;
	}
	/**
	 * Copies the files named in the JavaScript array from the module directory to the include directory
	 *
	 * @param array $scripts
	 *        	an array of script names without suffix
	 * @return boolean true on success, false on failure
	 */
	public function installJavaScripts(array $scripts) {
		$log = start_install_log ( "installJavaScripts" );
		foreach ( $scripts as $script ) {
			if (copy ( $this->src_path . "$script.js", IncPath . "/js/$script.js" ))
				$log->write ( "Installed functions of module" . $this->module, LOG_INFO );
			else
				return false;
		}
		return true;
	}
	/**
	 * Copies the files named in the templates array from the module directory to the template directory and renames them to /template/.tpl.php.
	 *
	 * @param array $templates
	 *        	an array of template names without suffix
	 * @return boolean true on success, false on failure
	 */
	public function installTemplates(array $templates) {
		$log = start_install_log ( "installTemplates" );
		foreach ( $templates as $template ) {
			if (copy ( $this->src_path . "$template.tpl.php", PagesPath . $template . ".tpl.php" ))
				$log->write ( "Installed template " . $template, LOG_INFO );
			else
				return false;
		}
		return true;
	}
	/**
	 * Copies the files named in the forms array from the module directory to the backend forms directory and renames them to /form/.form.php.
	 *
	 * @param array $forms
	 *        	an array of template names without suffix
	 * @param        	
	 *
	 * @return boolean true on success, false on failure
	 */
	public function installBackendForms(array $forms) {
		$log = start_install_log ( "installBackendForms" );
		foreach ( $forms as $form ) {
			if (copy ( $this->src_path . "$form.form.php", BasePath . "backend/forms/$form.form.php" ))
				$log->write ( "Installed form " . $form, LOG_INFO );
			else
				return false;
		}
		
		return true;
	}
	/**
	 * Adds items defined in backendmenue.json to the backend navigation bar.
	 *
	 * @param
	 *        	boolean true on success, false on failure
	 */
	public function installBackendNavEntry() {
		$log = start_install_log ( "installBackendNavEntry" );
		$current_menu = json_decode ( file_get_contents ( BasePath . "backend/menueItems.json" ), true );
		if (! $item_file = file_get_contents ( $this->src_path . "backendmenue.json" )) {
			$log->write ( "File not found.", LOG_ERROR );
			return false;
		} else
			$add_items = json_decode ( $item_file, true );
		
		if (key ( $add_items ['menueitem'] ) === 0) {
			foreach ( $add_items ['menueitem'] as $item ) {
				$current_menu ['menueitem'] [] = $item;
			}
		} else
			$current_menu ['menueitem'] [] = $add_items ['menueitem'];
		
		if (! file_put_contents ( BasePath . "backend/menueItems.json", json_encode ( $current_menu ) ))
			return false;
		else {
			return true;
			$log->write ( "Backend navigation of $this->module installed", LOG_INFO );
		}
	}
	
	/**
	 * Copies the file named styles.css from the module directory to the style directory and renames it to "modulename".css.
	 * In addition, an entry in the systems table for the style file is made.
	 *
	 * @param
	 *        	boolean true on success, false on failure
	 */
	public function installStyles() {
		$log = start_install_log ( "installStyles" );
		if (copy ( $this->src_path . "styles.css", BasePath . "styles/$this->module.css" )) {
			global $db;
			$qry = "INSERT INTO system ";
			$qry .= "(date,version,entry,value,grp,info) ";
			$qry .= "VALUES ('" . date ( "Y-m-d H:i:s" ) . "','" . $this->version . "','styles','" . $this->module . ".css','" . $this->group . "','" . $info . "');";
			// var_dump ( $qry );
			if (! $result = $db->query ( $qry )) {
				$log->write ( "Data base error: " . $db->error, LOG_ERROR );
				return false;
			} else
				$log->write ( "Installed styles of module " . $this->module, LOG_INFO );
		} else
			return false;
		
		return true;
	}
	/**
	 * Copies the file named backendstyles.css from the module directory to the backend directory and renames it to "modulename".b.css.
	 * In addition, an entry in the systems table for the style file is made.
	 *
	 * @param
	 *        	boolean true on success, false on failure
	 */
	public function installBackendStyles() {
		$log = start_install_log ( "installBackendStyles" );
		if (copy ( $this->src_path . "backendstyles.css", StylePath . $this->module . ".b.css" )) {
			global $db;
			$qry = "INSERT INTO system ";
			$qry .= "(date,version,entry,value,grp,info) ";
			$qry .= "VALUES ('" . date ( "Y-m-d H:i:s" ) . "','" . $this->version . "','backendstyles','" . $this->module . ".b.css','" . $this->module . "','" . $info . "');";
			// var_dump ( $qry );
			if (! $result = $db->query ( $qry )) {
				$log->write ( "Data base error: " . $db->error, LOG_ERROR );
				return false;
			} else
				$log->write ( "Installed backend styles of module " . $this->module, LOG_INFO );
		} else
			return false;
		
		return true;
	}
	/**
	 * Creates the administrator after the tables have been installed.
	 * If the system tables have not been installed yet, the function quits with an error.
	 * If an administrator already exists, FALSE is returned.
	 *
	 * @return boolean
	 */
	public function createAdmin() {
		$administrator = new Users ();
		if ($administrator->existAdmin ())
			return FALSE;
		
		if ($_POST ['admin'] == "create") {
			$admin ['uid'] = $_POST ['adminid'];
			$admin ['email'] = $_POST ['adminemail'];
			$admin ['pwd'] = $_POST ['pwd'];
			$admin ['role'] = "administrator";
			
			$administrator->insert ( $admin );
			self::insert_security_key ();
			echo '<div class="message success">';
			echo "Administrator created";
			echo '</div>';
			return TRUE;
		}
	}
	/**
	 * Shows the form for creating the first administrator, which is necessary to complete the installation.
	 */
	public function showAdminForm() {
		?>

<form style="width: 300px" action="" method="post">
	<label for="adminid">Administrator ID</label> <input type="text"
		name="adminid" id="adminid" required> <label for="adminemail">Email</label>
	<input type="email" name="adminemail" id="adminemail" required> <label
		for="pwd">Passwort</label> <input type="password" name="pwd" id="pwd"
		required> <input type="submit" name="submit" value="anlegen"> <input
		type="hidden" name="admin" value="create">
</form>

<?php
	}
	/**
	 * Write into the system table information about the system or module that has been installed.
	 *
	 * @param string $info
	 *        	used to leave a notice for the info field.
	 */
	public function updateSystemTable($info = "") {
		$db = db_init ();
		$log = start_install_log ( "updateSystemTable" );
		$qry = "INSERT INTO system ";
		$qry .= "(date,version,entry,value,grp,info) ";
		$qry .= "VALUES ('" . date ( "Y-m-d H:i:s" ) . "','" . $this->version . "','module','" . $this->module . "','" . $this->group . "','" . $info . "');";
		
		if (! $result = $db->query ( $qry ))
			$log->write ( "Data base error: " . $db->error, LOG_ERROR );
	}
	private function insert_security_key() {
		$key = openssl_random_pseudo_bytes ( 32 );
		$log = start_install_log ( "insert_security_key" );
		$db = db_init ();
		$qry = "INSERT INTO system ";
		$qry .= "(date,version,entry,value,grp,info) ";
		$qry .= "VALUES ('" . date ( "Y-m-d H:i:s" ) . "','1','sslkey','" . bin2hex ( $key ) . "','system','');";
		
		if (! $result = $db->query ( $qry )) {
			$log->write ( "Data base error: " . $db->error, LOG_ERROR );
			die ();
		}
	}
	/**
	 * source a file with SQL commands
	 *
	 * @param unknown $file
	 *        	name and path of the file containing the sql commands
	 */
	public static function sourceQryFile($file) {
		global $user, $password, $database, $host;
		$log = start_install_log ( "sourceQryFile" );
		if (DBtype == "mysql")
			$sqlCommand = "mysql -u $user -p$password -D $database -h $host < $file";
		elseif (DBtype == "psql")
			$sqlCommand = "psql '-user=$user -dbname=$database -host$host -port=5432 -password=$password' -f $file";
		$answer = shell_exec ( $sqlCommand );
		$log->write ( $answer, LOG_INFO );
	}
}