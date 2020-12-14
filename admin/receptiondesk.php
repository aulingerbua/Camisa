<?php
require '../plug.php';

include_once BasePath . 'html_head.php';

$InName = new Guests();
//var_dump($_POST);
if ( $InName->checkInvitation($_GET ['uid']) ) {
	if ( $_POST['update'] ) {
		if ( $InName->register($_POST, $_GET['uid']) ) {
			$InName->confirm();
		} else {
			$InName->error();
			$InName->registerForm();
		}
	} else {
		$InName->registerForm();
	}
} else {
	echo '<p style="color:red;padding:50px">Ungültiger Einladungscode.</p>';
}

include_once BasePath . 'html_foot.php';
?>