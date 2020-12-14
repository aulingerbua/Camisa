<?php
/*
 * manage user profile
 */
$profil = new Profiles ();

$chkdel = $profil->dataBaseIo();

if ( is_string($chkdel) ) {
	echo $chkdel;
}

// show list of user names to Admin
/* if ($_SESSION ['level'] < 0 && ! empty ( $_GET ['member'] )) {
	$userlist = $profil->nameList ();
	echo "<h3>Mitglieder</h3>";
	echo '<div id="titleListWindow">';
	foreach ( $userlist as $lname ) {
		echo '<li><a href="?member=' . $lname . '">' . $lname . '</a></li>';
	}
	echo '</div>';
} */

$formVals = $_GET ['profile'] ? $profil->retrieve ( $_GET ['profile'] ) : NULL;
$formVals = $formVals [0];
$profil->showForm ( $formVals );

?>

