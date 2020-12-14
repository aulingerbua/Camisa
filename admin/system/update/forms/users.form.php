<?php
/*
 * user administration
 */

$mitglied = new Users ();

$chkdel = $mitglied->dataBaseIo();

if ( $chkdel ) {
	echo $chkdel;
}

// Liste der Mitglieder anzeigen
$userlist = $mitglied->nameList (); ?>
<h3>Mitglieder</h3>
<div id="titleListWindow">
<ul>
<?php 
foreach ( $userlist as $lname ) {
	echo '<li><a href="?tool=member&member=' . $lname . '">' . $lname . '</a></li>';
}
?>
</ul>
</div>
<?php
$formVals = $_GET ['member'] ? $mitglied->retrieve ( $_GET ['member'] ) : NULL;
$formVals = $formVals [0];
$mitglied->showForm ( $formVals );
