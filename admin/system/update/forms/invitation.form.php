<?php
/*
 * invite new user
 */


$Einlad = new Guests ();
//var_dump ( $_POST );
if ($_POST ['insert']) {
	if ($Einlad->invite ( $_POST )) {
		echo $Einlad->confirm ();
	} else {
		echo $Einlad->error ();
	}
	echo $Einlad->warning();
} else {
	$Einlad->showForm ();
}

?>

