<?php
class Pages extends Registry {
	function __construct() {
		parent::setTable ( 'pages' );
		parent::setUniqueField ( 'name' );
		parent::setOrderBy ( 'rank' );
	}
	/**
	 * Displays the standard form.
	 *
	 * @param array $iniValues        	
	 */
	public function showForm(array $iniValues = NULL) {
		// displays the defaulf form for this class
		$form [] = '<form action="" class="editor" method="post">';
		$form [] = '<label for="name">Name</label>';
		$form [] = '<input type="text" id="name" name="name" value="' . $iniValues ['name'] . '">';
		$form [] = '<label for="template">Vorlage</label>';
		$t = (new Templates ())->distinct ( 'template' );
		$template = '<select id="template" name="template">';
		foreach ( $t as $tem ) {
			$template .= '<option value="' . $tem . '" ';
			if ($iniValues ['template'] == $tem) {
				$template .= 'selected';
			}
			$template .= '>' . $tem . '</option>';
		}
		$form [] = $template . '</select>';
		$form [] = '<label for="parent">übergeordnet</label>';
		$p = self::nameList ();
		$parent = '<select id="parent" name="parent">
						<option value="">Keine</option>';
		foreach ( $p as $par ) {
			$parent .= '<option value="' . $par . '"';
			if ($iniValues ['parent'] == $par) {
				$parent .= ' selected';
			}
			$parent .= '>' . $par . '</option>';
		}
		$form [] = $parent . '</select>';
		$form [] = '<label for="rank">Reihenfolge</label>';
		if (empty ( $iniValues ['rank'] )) {
			$rank = 0;
		} else {
			$rank = $iniValues ['rank'];
		}
		$form [] = '<input type="number" id="rank" name="rank" max="10" value="' . $rank . '" min="0">
						';
		$form [] = '<label for="status">Status</label>';
		$form [] = '<select id="status"	name="status">
						<option value="public">sichtbar</option>
						<option value="draft">Entwurf</option>
						<option value="archive">Archiv</option>
					</select>';
		$form [] = '<label for="permit">Zugang</label>';
		$pp = (new Users ())->roList ();
		$permit = '<select id="permit" name="permit">';
		$permit .= '<option value="10">Alle</option>';
		for($i = 0; $i < count ( $pp ); $i ++) {
			$permit .= '<option value="' . $pp [$i] ['level'] . '" ';
			if ($iniValues ['permit'] == $pp [$i] ['level']) {
				$permit .= 'selected';
			}
			$permit .= '>' . $pp [$i] ['role'] . '</option>';
		}
		$form [] = $permit . '</select>';
		if ($iniValues) {
			$form [] = Registry::makeUpdateButton ();
			$form [] = Registry::makeDeleteButton ();
			$form [] = '<input type="hidden" id="id" name="id" value="' . $iniValues ['id'] . '">';
			$form [] = '<input type="hidden" id="page" name="page" value="' . $iniValues ['page'] . '">';
		} else {
			$form [] = Registry::makeInsertButton ();
		}
		$form [] = '</form>';
		
		echo implode ( "\n", $form );
	}
	public function findChildren($parent) {
		/*
		 * find children of Pages
		 */
		$children = self::retrieve ( [ 
				parent => $parent 
		], NULL, $this->uniquefield );
		while ( $children ) {
			$ch [] = array_shift ( $children ) [$this->uniquefield];
		}
		return $ch;
	}
	public function getTemplate($page) {
		// get the template of the requested page
		global $db;
		
		$qry = "select template, module, permit from templates";
		$qry .= " where template = ( select template from pages where name = '$page' )";
		
		if ($result = $db->query ( $qry )) {
			$data = $result->fetch_assoc ();
		} else {
			echo "Fehler in getTemplate! " . $db->error;
		}
		
		return $data;
	}
	/**
	 * Checks if the page is visible to Users with the specified user level.
	 * 
	 * @param unknown $page        	
	 * @param unknown $user_level        	
	 * @return boolean
	 */
	public function visible($page, $user_level = NULL) {
		if (is_null ( $page )) {
			return TRUE;
		} else {
			global $db;
			
			$qry = "select permit from pages where name = '$page'";
			
			if ($result = $db->query ( $qry )) {
				$data = $result->fetch_assoc ();
			} else {
				echo "Fehler in visible! " . $db->error;
			}
			
			$user_level = $user_level ? $user_level : 10;
			if ($data ['permit'] >= $user_level) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	/**
	 * Gets the pages with editable text, i.e.
	 * where the template is non empty.
	 *
	 * @return array|NULL list of the editable pages.
	 */
	public function editablePages() {
		$log = new Log ( "editablePages" );
		$qry = "SELECT name FROM pages JOIN templates ON (pages.template = templates.template) WHERE templates.module != 'empty';";
		try {
			$data = self::executeQuery ( $qry );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage (), LOG_WARNING );
			return NULL;
		}
		foreach ( $data as $d )
			$pages [] = $d ['name'];
		return $pages;
	}
}
