<?php
require '../plug.php';
if (! ($_SERVER ['HTTP_REFERER'] == URI . "/login?" || $_POST)) {
	// header('HTTP/1.1 403 Forbidden');
	header ( "LOCATION: " . HOME . "login?forbidden" );
	exit ();
}

include_once BasePath . 'html_head.php';

$success = FALSE;
$pwdchange = new Admission ();
if ($_POST ['submit'] == "speichern") {
	if ($pwdchange->resetPwd ( $_POST ['uid'], $_POST ['email'], $_POST ['pwd'] )) {
		$success = TRUE;
		echo "<p class='notice'>Passwort geändert<br><a href='/'>Zurück zu " . SITE . "</a></p>\n";
	}
}
?>
<?php if (!$success) : ?>
<form id="login-form" action="?" method="POST">
	<label for="uid">Benutzername</label> <input type="text" id="uid"
		name="uid" autofocus required> <label for="email">Email</label> <input
		type="email" id="email" name="email" required><label for="pwd">neues Passwort</label>
	<input type="password" id="pwd" name="pwd" required>
		<?php
	echo $pwdchange->error();
	?>
		<input type="submit" name="submit" value="speichern">

</form>
<?php endif;?>
<?php include_once BasePath . 'html_foot.php';?>