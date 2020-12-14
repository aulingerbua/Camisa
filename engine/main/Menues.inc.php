<?php
/** This class contains functions to create lists of titles or Pages with links automatically
 * to be used as Menues. Currently only two levels are possible.
 * @author Armin Aulinger
 *
 */
class Menues {
	// array keys for the first, second list level
	protected $firstLevel; //!< @var string
	protected $secLevel; //!< @var string
	protected $exclude = NULL; //!< @var array|NULL items to exclude from the menue
	// store name for the first level
	protected $head;
	// rank by which the list items will be ordered
	protected $rank;
	// the name for the link
	protected $linkName = NULL;
	// the table to read menue data from
	protected $table;
	protected function setLevels($first, $second = NULL) {
		$this->firstLevel = $first;
		$this->secLevel = $second;
	}
	protected function setExclude($exclude) {
		$this->exclude = $exclude;
	}
	protected function setHead($head) {
		$this->head = $head;
	}
	protected function setRank($rk) {
		$this->rank = $rk;
	}
	protected function setLink($name) {
		$this->linkName = $name;
	}
	protected function setTable($table) {
		$this->table = $table;
	}
	// should current entry be highlighted
	protected $highlight;
	// should list be ordered
	protected $ordered;
	protected function setHighlight($hl) {
		$this->highlight = $hl;
	}
	protected function setListtype($od) {
		$this->ordered = $od;
	}
	private function isCurrent($contentName) {
		/*
		 * check if the displayed content belongs
		 * to the current menue item.
		 */
		$currentLink = explode ( "&", $this->linkName );
		$currentLink = array_pop ( $currentLink );
		if ($this->highlight && $_GET [$currentLink] == $contentName) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	// make sublist
	private function subList(array $a) {
		if (isset ( $a [$this->secLevel] ) && $a [$this->firstLevel] == $this->head) {
			$sub = '<li';
			if (self::isCurrent ( $a [$this->secLevel] )) {
				$sub .= ' id="current"';
			}
			if ($this->linkName == "page") {
				$sub .= '><a href="' . $a [$this->secLevel] . '.htm">';
			} elseif (empty ( $this->linkName )) {
				$sub .= '><a href="' . $a [$this->secLevel] . '.htm">';
			} else {
				$sub .= '><a href="?' . $this->linkName . '=' . $a [$this->secLevel] . '">';
			}
			$sub .= $a [$this->secLevel] . "</a></li>";
			return $sub;
		} else {
			return NULL;
		}
	}
	
	// walk the array and create the menue
	protected function createMenue($menue, $levels, $headlink = TRUE) {
		if (! $menue)
			return null;
		
		if ($this->ordered) {
			$Ltype = "ol";
		} else {
			$Ltype = "ul";
		}
		/*
		 * loop over levels (the primary list entries)
		 * then search the sublevels that belong to the
		 * primary level
		 */
		foreach ( $levels as $L ) {
			self::setHead ( $L );
			$sub = array_filter ( array_map ( "self::subList", $menue ) );
			$sub = implode ( "\n", $sub );
			if ($headlink) {
				echo '<li';
				if (self::isCurrent ( $L )) {
					echo ' id="current"';
				}
				if ($this->linkName == "page") {
					echo '><a href="' . $L . '.htm">';
					echo $L . "</a>\n";
				} elseif (empty ( $this->linkName )) {
					echo '><a href="' . $L . '.htm">';
					echo $L . "</a>\n";
				} else {
					echo '><a href="?' . $this->linkName . '=' . $L . '">';
					echo $L . "</a>\n";
				}
			} else {
				echo "<li>$L\n";
			}
			if ($sub) {
				echo "<$Ltype>\n";
				print ($sub) ;
				echo "</$Ltype>\n";
				echo "</li>\n";
			} else {
				echo "</li>\n";
			}
		}
	}
}