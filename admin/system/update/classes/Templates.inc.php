<?php
/**
 * @author armin
 *
 */
class Templates extends Registry {
	function __construct() {
		parent::setTable ( 'templates' );
		parent::setUniqueField ( 'template' );
		parent::setOrderBy ( 'id' );
		parent::setButtons ( [ 
				'update' => 'registrieren',
				'delete' => 'entfernen' 
		] );
	}
	/**
	 * show form for template registration
	 *
	 * @param array $iniValues
	 *        	preset values for form elements
	 */
	public function showForm($iniValues = NULL) {
		$form [] = '<form class="editor" action="" method="POST">';
		$form [] = '<ul>';
		$form [] = '<li><p>Neues Template registrieren</p></li>';
		$form [] = '<li><label for="template">Template Name</label>';
		$form [] = self::templatesDropdown ( PagesPath, "tpl", "template" ) . "</li>";
		$form [] = '<li><label for="module">Template Modul</label>';
		$form [] = '<select id="module"	name="module">
				<option value="">Wählen Sie ein Modul</option>
				<option value="single">Einzeltext</option>
				<option value="multi">Multitext</option>
				<option value="empty">leer</option>
					</select></li>';
		$form [] = self::makeUpdateButton ();
		$form [] = self::makeDeleteButton ();
		$form [] = self::makeQuitButton ();
		$form [] = '</ul>';
		$form [] = '</form>';
		echo implode ( "\n", $form );
	}
	/*
	 * public function delete($which) {
	 * $tpl = get_registered_templates();
	 * foreach ($tpl as $t) {
	 * if ($_POST['template'] == $t['template']) {
	 * var_dump($t);
	 * parent::delete($t['id']);
	 * return true;
	 * }
	 * }
	 * return false;
	 * }
	 */
	public function update(array $input, $which) {
		$tpl = get_registered_templates ();
		foreach ( $tpl as $t ) {
			if ($input ['template'] == $t ['template']) {
				parent::update ( $input, $input ['id'] );
				return true;
			}
		}
		parent::insert ( $input );
	}
	/**
	 * reads the files found in $path and returns the html code of a selection element.
	 *
	 * @param string $path
	 *        	path of the directory
	 * @param string $pattern
	 *        	pattern in the file names to be matched
	 * @param string $name
	 *        	name and id of the selection element
	 * @return void|string
	 */
	public static function templatesDropdown($path, $pattern, $name) {
		if (! $path)
			return;
		$dropdown [] = "<select id='$name' name='$name'>";
		$dropdown [] = "<option value=''>Wählen Sie ein Template</option>";
		$dir = new DirectoryIterator ( $path );
		foreach ( $dir as $file ) {
			if ($file->isFile () && preg_match ( "/$pattern/", $file->getFilename () )) {
				$name = $file->getBasename ( ".tpl.php" );
				$dropdown [] = "<option value='$name'>$name</option>";
			}
		}
		$dropdown [] = "</select>";
		return implode ( "\n", $dropdown );
	}
}
?>