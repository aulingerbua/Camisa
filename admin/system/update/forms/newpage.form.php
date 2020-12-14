<?php
/*
 * create a new page
 */

$pagesEd = new Pages();
$toc = new PageMenue("newpage");

// $chkdel = $pagesEd->dataBaseIo();

// if ( is_string($chkdel) ) {
// 	echo $chkdel;
// }

handle_db_input($pagesEd);

echo "<h3>Seiten</h3>";
echo '<div id="titleListWindow">';
$toc->createM();
echo '</div>';

$formVals = $_GET ['newpage'] ? $pagesEd->retrieve ( [name=>$_GET ['newpage']] )[0] : NULL;
$pagesEd->showForm($formVals);

?>