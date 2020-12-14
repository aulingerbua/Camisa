<?php
/** 
 * Static classes to remove the files, data bases and data base entries of a particular module.
 * 
 * @author Armin Aulinger
 *
 */
class RemoveModules {
	/**
	 * Remove the tables of a module.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string|array $tables
	 *        	name(s) of the tables.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function tables($tables = NULL) {
		If (! $tables)
			return TRUE;
		$db = db_init ();
		$log = start_install_log ( "RemoveModules:tables" );
		
		if (is_array ( $tables )) {
			foreach ( $tables as $tab )
				$qry [] = "DROP TABLE $tab";
			$res = $db->multi_query ( implode ( ";", $qry ) );
		} else
			$res = $db->query ( "DROP TABLE $tables" );
		
		if (! $res) {
			$log->write ( $db->error, LOG_ERROR );
			return FALSE;
		}
		
		return TRUE;
	}
	/**
	 * Remove entries in the system table.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $module
	 *        	name of the module
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function systemEntries($module = NULL) {
		if (! $module)
			return TRUE;
		$db = db_init ();
		$log = start_install_log ( "RemoveModules:systemEntries" );
		
		if (! $db->query ( "DELETE FROM system WHERE grp='$module'" )) {
			$log->write ( $db->error, LOG_ERROR );
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * Unlink the functions php file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $module
	 *        	name of the module.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function functions($module = NULL) {
		if (! $module)
			return TRUE;
		$log = start_install_log ( "RemoveModules:functions" );
		
		if (! unlink ( IncPath . $module . ".inc.php" )) {
			$log->write ( "Could not delete the function file of module $module.", LOG_ERROR );
			return FALSE;
		}
		
		return TRUE;
	}
	/**
	 * Unlink the AJAX php file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $module
	 *        	name of the module.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function AJAXfile($module = NULL) {
		if (! $module)
			return TRUE;
			$log = start_install_log ( "RemoveModules:AJAXfile" );
			
			if (! unlink ( ajaxPath . $module . ".ajax.php" )) {
				$log->write ( "Could not delete the AJAX file of module $module.", LOG_ERROR );
				return FALSE;
			}
			
			return TRUE;
	}
	/**
	 * Unlink the javaScript file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $module
	 *        	name of the module.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function JSfile($module = NULL) {
		if (! $module)
			return TRUE;
			$log = start_install_log ( "RemoveModules:JSfile" );
			
			if (! unlink ( JsPath . $module . ".js" )) {
				$log->write ( "Could not delete the javaScript file of module $module.", LOG_ERROR );
				return FALSE;
			}
			
			return TRUE;
	}
	/**
	 * Unlink the style file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $module
	 *        	name of the module.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function styles($module = NULL) {
		if (! $module)
			return TRUE;
		$log = start_install_log ( "RemoveModules:styles" );
		if (!file_exists(BasePath . "/styles/" . $module . ".css"))
			return TRUE;
		
		if (! unlink ( BasePath . "/styles/" . $module . ".css" )) {
			$log->write ( "Could not delete the styles file of module $module.", LOG_ERROR );
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * Unlink the templates php file and remove their entries in the templates and pages table.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string|array $template
	 *        	name of the template(s).
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function templates($template = NULL) {
		if (! $template)
			return TRUE;
		$log = start_install_log ( "RemoveModules:templates" );
		$db = db_init ();
		
		if (is_array ( $template )) {
			foreach ( $template as $temp ) {
				$del = unlink ( PagesPath . $temp . ".tpl.php" );
				$res = $db->query ( "DELETE FROM templates WHERE template='$temp';" );
				$res = $db->query ( "DELETE FROM pages WHERE template='$temp';" );
			}
		} else {
			$del = unlink ( PagesPath . $template . ".tpl.php" );
			$res = $db->query ( "DELETE FROM templates WHERE template='$template';" );
			$res = $db->query ( "DELETE FROM pages WHERE template='$template';" );
		}
		if (! $del)
			$log->write ( "Could not delete the template files of module.", LOG_ERROR );
		if (! $res)
			$log->write ( $db->error, LOG_ERROR );
		return $res && $del;
	}
	/**
	 * Unlink the backend forms php file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string $form
	 *        	name(s) of the form.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function backendForm($form = NULL) {
		if (! $form)
			return TRUE;
		$log = start_install_log ( "RemoveModules:backendForm" );
		
		if (is_array ( $form )) {
			foreach ( $form as $f )
				$del = unlink ( BasePath . "backend/forms/" . $f . ".form.php" );
		} else
			$del = unlink ( BasePath . "backend/forms/" . $form . ".form.php" );
		
		if (! $del) {
			$log->write ( "Could not delete the backendForm of module.", LOG_ERROR );
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * Remove items from the backend menue JSON file.
	 *
	 * Writes an error message into the install log on failiure.
	 *
	 * @param string|array $entry
	 *        	name(s) of the entry.
	 * @return boolean TRUE on success, FALSE on error
	 */
	public static function backendNavEntry($entry = NULL) {
		if (! $entry)
			return TRUE;
		$log = start_install_log ( "RemoveModules:backendNavEntry" );
		$saved = FALSE;
		try {
			$current_menu = json_decode ( file_get_contents ( BasePath . "backend/menueItems.json" ), true );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage (), LOG_ERROR );
			return FALSE;
		}
		if (!is_array ( $entry ))
			$entry = [$entry];
		foreach ($current_menu['menueitem'] as $me)
			if (! in_array($me['entry'], $entry))
				$new_menu['menueitem'][] = $me;
				
		if (! empty ( $new_menu )) {
			$menu_js = json_encode ( $new_menu );
			$saved = file_put_contents ( BasePath . "backend/menueItems.json", $menu_js );
		}
		
		if (! $saved)
			$log->write ( "menueItems.json could not be saved", LOG_WARNING );
		return $saved;
	}
}