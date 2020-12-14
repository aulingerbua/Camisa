<?php
/*
 * upload and register new Templates
 */

$tregister = new Templates ();

$chkdel = $tregister->dataBaseIo();

if ( $chkdel ) {
	echo $chkdel;
}

$tregister->showForm ();

?>

