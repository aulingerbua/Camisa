<?php
/**
 * Handles the update of the main system and additional modules.
 * @author Armin Aulinger
 *
 */
class Update extends Install {
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
	 * Checks if the installed version is older than the update.
	 *
	 * @return boolean true if the installed version is newer than the update.
	 */
	public function isInstalled() {
		$db = db_init ();
		$qry = "SELECT version FROM system WHERE entry ='module' AND value = '$this->module';";
		$result = $db->query ( $qry );
		$versionNumber = function ($v) {
			$vsep = explode ( ".", $v );
			$fac = 10;
			$vnumber = 0;
			for($i = 0; $i < count ( $vsep ); $i ++)
				$vnumber += $vsep [$i] * $fac / pow ( 10, $i );
			return $vnumber;
		};
		if (! $result)
			return FALSE;
		$installedVersion = $result->fetch_row ();
		
		return $versionNumber ( $installedVersion [0] ) >= $versionNumber ( $this->version );
	}
	/**
	 * Updates the class files
	 *
	 * @return boolean true on success
	 */
	public function updateClasses() {
		if ($this->module != "system") {
			$source = ModulesPath . "$this->module/update/";
			$target = BasePath . "engine/";
		} else {
			$source = SystemUpdatePath . "classes/";
			$target = BasePath . "engine/main/";
		}
		$type = "inc";
		return self::copySystemFiles ( $type, $source, $target );
	}
	/**
	 * Updates the form files
	 *
	 * @return boolean true on success
	 */
	public function updateForms() {
		if ($this->module != "system") {
			$source = ModulesPath . "$this->module/update/";
		} else {
			$source = SystemUpdatePath . "forms/";
		}
		$target = BasePath . "backend/forms/";
		$type = "form";
		return self::copySystemFiles ( $type, $source, $target );
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
	 * Not to be used here. Returns false.
	 *
	 * @return boolean false
	 */
	public function createAdmin() {
		return false;
	}
	/**
	 * Not to be used here. Returns false.
	 *
	 * @return boolean false
	 */
	public function showAdminForm() {
		return false;
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
		$qry = "UPDATE system SET ";
		$qry .= "date = '" . date ( "Y-m-d H:i:s" ) . "', ";
		$qry .= "version = '" . $this->version . "', ";
		$qry .= "info = '$info' ";
		$qry .= "WHERE entry = 'module' AND module = '" . $this->module . "';";
		
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
			$sqlCommand = "psql '-user=$user -dbname=$database -host=$host -port=5432 -password=$password' -f $file";
		$answer = shell_exec ( $sqlCommand );
		$log->write ( $answer, LOG_INFO );
	}
}