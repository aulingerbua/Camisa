<?php
/*
 * fill text Pages with content
 */
$inhalt = new Textpage($_GET['page']);
$module = $inhalt->getPageModule();

handle_db_input($inhalt);

if ($module == 'multi') {
    echo '<div id="titleListWindow">';
    $inhalt->chapterLinkList();
    echo "</div>";
    $iniVal = $inhalt->retrieveByChapter($_GET['chapter'])[0];
} else
    $iniVal = $inhalt->retrieveByPage()[0];
$iniVal['type'] = $module;
$inhalt->showForm($iniVal);

?>