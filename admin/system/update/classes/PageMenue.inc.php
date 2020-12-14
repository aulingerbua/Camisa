<?php
class PageMenue extends Menues {
	function __construct($link = "page") {
		/*
		 * the first level in the list are categories
		 * the second one are titles
		 */
		parent::setLevels ( "parent", "name" );
		parent::setRank ( "rank" );
		parent::setTable ( "pages" );
		parent::setLink ( $link );
	}
	public function createM($exclude = NULL, $highlight = TRUE, $ordered = FALSE, $headlink = TRUE) {
		global $db;
		
		parent::setHighlight ( $highlight );
		parent::setListtype ( $ordered );
		
		if (is_array ( $exclude )) {
			$no = implode ( ", ", $exclude );
			$exclude = "AND $this->secLevel NOT IN ( '$no' ) ";
		} elseif ($exclude) {
			$exclude = "AND $this->secLevel != '$exclude'";
		}
		
		$user_level = $_SESSION ['level'] ? $_SESSION ['level'] : 10;
		
		$qry = "SELECT $this->firstLevel, $this->secLevel FROM $this->table WHERE permit >= $user_level
		 $exclude ORDER BY $this->rank";
		
		// echo "<p>$qry</p>";
		
		if ($result = $db->query ( $qry )) {
			while ( $row = $result->fetch_assoc () ) {
				$data [] = $row;
			}
		} else {
			die ( "Fehler in query! " . $db->error );
		}
		// var_dump($data);
		/*
		 * split the data into an array
		 * with the entries having no parent
		 * to become levels and Pages that have a parent
		 * and will become the sublist items
		 */
		for($i = 0; $i < count ( $data ); $i ++) {
			if (! $data [$i] [$this->firstLevel]) {
				$menue [$i] [$this->firstLevel] = $data [$i] [$this->secLevel];
				$levels [$i] = $data [$i] [$this->secLevel];
			} else {
				$menue [$i] [$this->firstLevel] = $data [$i] [$this->firstLevel];
				$menue [$i] [$this->secLevel] = $data [$i] [$this->secLevel];
			}
		}
		parent::createMenue ( $menue, $levels, $headlink = $headlink );
	}
}