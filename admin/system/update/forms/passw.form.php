<?php
/*
 * change password
 */
$mitglied = new Password ();
$bv = $mitglied->getButtonValues ( 'update' );
//var_dump($udata);
/*
 * if ($_POST ['update'] == $mitglied->getButtonValues ( 'update' )) {
 * //var_dump($_POST);
 * if (! $mitglied->update ( $_POST, current_user () )) {
 * $mitglied->error ();
 * }
 * }
 */
$check = $mitglied->dataBaseIo ();
if (! (TRUE === $check || empty ( $check )))
	$mitglied->error ();
$mitglied->showForm ();

?>

