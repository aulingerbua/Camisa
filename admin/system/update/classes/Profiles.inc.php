<?php
class Profiles extends Users {
	
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see Users::showForm()
	 */
	public function showForm($iniValues = NULL) {
		// displays the default form for this class
		$form [] = '<form action="" class="editor" method="post">';
		$form [] = '<div class="box-inside-form">';
		$form [] = '<label for="name">Name</label>';
		$form [] = '<input type="text" id="name" name="name" value="' . $iniValues ['name'] . '">';
		$form [] = '<label for="street">Strasse</label>';
		$form [] = '<input type="text" id="street" name="street" value="' . $iniValues ['street'] . '">';
		$form [] = '<label for="areacode">PLZ</label>';
		$form [] = '<input type="text" id="plz" name="areacode" value="' . $iniValues ['areacode'] . '">';
		$form [] = '<label for="city">Stadt</label>';
		$form [] = '<input type="text" id="city" name="city" value="' . $iniValues ['city'] . '">';
		$form [] = '<label for="tel">Telefon</label>';
		$form [] = '<input type="text" id="tel" name="tel" value="' . $iniValues ['tel'] . '">';
		$form [] = '<label for="mobile">Mobil</label>';
		$form [] = '<input type="text" id="mobile" name="mobile" value="' . $iniValues ['mobile'] . '">';
		$form [] = '<label for="email">Email</label>';
		$form [] = '<input type="text" id="email" name="email" value="' . $iniValues ['email'] . '">';
		$form [] = '<label for="homepage">Homepage</label>';
		$form [] = '<input type="text" id="homepage" name="homepage" value="' . $iniValues ['homepage'] . '">';
		$form [] = '</div>';
		$form [] = '<div class="box-inside-form">';
		$form [] = '<label for="description">Beschreibung</label>';
		$form [] = '<textarea id="editor" name="description">' . $iniValues ['description'] . '</textarea>';
		$form [] = '</div><div style="clear:both"></div>';
		if ($iniValues) {
			$form [] = self::makeUfHiddenElement ( current_user () );
			$form [] = self::makeUpdateButton ();
			$form [] = self::makeDeleteButton ();
		} else {
			$form [] = self::makeUfHiddenElement ( current_user () );
			$form [] = self::makeInsertButton ();
		}
		
		$form [] = '</form>';
		
		echo implode ( "\n", $form );
	}
	/**
	 * Returns a list with the full member names.
	 * 
	 * @return an
	 */
	public function memberNames() {
		$names = self::retrieve ( NULL, NULL, "name" );
		return $names ['name'];
	}
}
?>
