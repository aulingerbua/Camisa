<?php
class textMenue extends Menues {
	function __construct($link = "chapter") {
		/*
		 * the first level in the list are categories
		 * the second one are titles
		 */
		parent::setLevels ( "category", "title" );
		parent::setRank ( "chapter" );
		parent::setTable ( "textpages" );
		parent::setLink ( $link );
	}
	public function createM($page, $highlight = TRUE, $ordered = TRUE, $headlink = FALSE) {
		/*
		 * The $headlink defines whether the
		 * first level should be displayed as link.
		 */
		global $db;
		
		parent::setHighlight ( $highlight );
		parent::setListtype ( $ordered );
		
		$qry = "SELECT $this->firstLevel, $this->secLevel, $this->rank FROM $this->table WHERE page = '$page' ORDER BY '$this->rank'";
		
		// echo "<p>$qry</p>";
		
		if ($result = $db->query ( $qry )) {
			while ( $row = $result->fetch_assoc () ) {
				$data [] = $row;
			}
		} 

		else {
			die ( "Fehler in query! " . $db->error );
		}
		
		$levels = Textpage::hasCategories ( "$page" );
		parent::createMenue ( $data, $levels, $headlink = $headlink );
	}
}