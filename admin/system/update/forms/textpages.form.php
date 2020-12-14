<?php
/*
 * fill text Pages with content
 */
$inhalt = new Textpage ();

// $chkdel = $inhalt->dbManipBlock();

if ($chkdel = $inhalt->dataBaseIo()) {
	echo $chkdel;
}

// Der "new-Button" nur für multi-text Pages
/*
 * if ($chapter = $module === "multi") {
 * echo '<form action="" class="editor" method="post">';
 * echo '<input type="submit" id="new" name="new" value="neues Kapitel">';
 * echo '</form>';
 * }
 */

// Beiträge in extra Fenster auflisten,
// falls es mehr als 0 Kapitel pro Seite gibt
// (module multi)
/*
 * if ($chapter) {
 * $titles = $inhalt->titleList ( $page );
 * echo "<h3>Kapitel</h3>";
 * echo '<div id="titleListWindow">';
 * echo '<ul>';
 * for($l = 0; $l < count ( $titles ); $l ++) {
 * echo '<li><a href="?page='.$page.'&title=' . $titles [$l]['title'] . '">' . $titles [$l]['title'] . '</a></li>'."\n";
 * }
 * echo '</ul>
 * </div>';
 * if ($_POST ['new'] != "neues Kapitel") {
 * $formVals = $inhalt->retrieveByTitle ( $_GET ['title'] )[0];
 * }
 * }
 * elseif ($_POST ['new'] != "neues Kapitel") {
 * $formVals = $inhalt->retrieveByPage( $page )[0];
 * }
 */

$inhalt->showForm ( $inhalt->retrieveByPage ( $page ) [0] );

?>