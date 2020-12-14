<?php
require 'plug.php';

session_start ();

if (isset ( $_GET ['forbidden'] )) {
	$message = '<p class="alert">Nicht angemeldet.</p>';
} else if (isset ( $_GET ['logout'] )) {
	if (Admission::logout ()) {
		$message = '<p class="notice">Erfolgreich abgemeldet.</p>';
	}
}

if ($_POST ['uid'] && $_POST ['pwd']) {
	
	session_start ();
	
	$admis = new Admission ();
	
	if ($admis->grantAccess ( $_POST ['uid'], $_POST ['pwd'] )) {
		header ( "LOCATION: " . HOME );
	} else {
		$message = '<p class="alert">' . $admis->getError () . '</p>';
	}
}

//include_once 'html_head.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<title><?=SITE?></title>
<meta charset="UTF-8">
<meta name="author" content="Armin Aulinger">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="/styles/styles.css">
</head>

<body>

<form id="login-form" action="?" method="POST">
	<div class="camisa-logo">
		<img alt="CaMiSa banner" src="<?=ImagesPath?>Camisa_logo.svg">
	</div>
	<label for="uid">Benutzername</label> <input type="text" id="uid"
		name="uid" autofocus required> <label for="pwd">Passwort</label> <input
		type="password" id="pwd" name="pwd" required>
<?php
echo $message;
?>
		<input type="submit" name="submit" value="einloggen">
	<p>
		<a href="/">Zurück zu <?=SITE?></a>
	</p>
</form>
</body>
</html>
