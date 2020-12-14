<?php
/*
 * upload and register new Templates
 */

$tregister = new Templates ();

handle_db_input($tregister);

$tregister->showForm ();

?>

