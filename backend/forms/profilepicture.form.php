<?php
/*
 * upload the profile picture
 */
require IncPath . 'upload.inc.php';
require IncPath . 'Profiles.inc.php';

$mitglied = new Profiles ();
$profpic = new pictures ();

$udata = $mitglied->retrieve ( $_SESSION ["user"] );
$udata = $udata [0];
$picpath = 'img/profpics/';
$completepath = BasePath . $picpath;

if ($_POST ["upload"] == "einfügen") {
	// var_dump($_POST);
	
	if ($filename = $profpic->upload ( $completepath )) {
		$file = $picpath . $filename;
		echo "<p>$file</p>";
		$mitglied->update ( array (
				picture => $file 
		), $_SESSION ['user'] );
	} else {
		echo '<p class="alert">' . $profpic->error () . '</p>';
	}
}

$profpic->showForm ( $udata ['picture'] );

?>

