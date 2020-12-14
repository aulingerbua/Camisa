<?php
require '../plug.php';
session_start ();

/* if ((new Users ())->tableExists ()) {
	if (! ($_SESSION ["grant"] === sslK () && $_SESSION ['level'] <= 0)) {
		// header('HTTP/1.1 403 Forbidden');
		header ( "LOCATION: " . HOME . "login?forbidden" );
		exit ();
	}
} */

?>
<!DOCTYPE html>
<html lang="de">
<head>
<title>install</title>
<meta charset="UTF-8">
<style type="text/css">
label {
	display: block;
}

.camisaBanner {
	width: 500px;
	margin: 100px auto 20px auto;
}

.message {
	margin: 20px auto 20px auto;
	font-family: arial sans-serif;
	padding: 5px;
	border: 1px solid #012022;
	width: 500px;
}

.success {
	background-color: #6bf66c;
}

.fail {
	background-color: #f66b6b;
}

.omit {
	background-color: #02acbb;
}

#installbutton {
	margin: 20px auto 20px auto;
	font-family: arial sans-serif;
	width: 100px;
	text-align: center;
	display: block;
	padding: 8px;
	font-size: 15pt;
	color: #00f;
	background: #ff0;
	text-decoration: none;
	border: 1px outset #00a1ff;
}

#installbutton:hover, #installbutton:active {
	border: 1px inset #00a1ff;
}
</style>
</head>
<body>
	<div class="camisaBanner">
		<img alt="CaMiSa banner" src="../img/installBanner1.png">
	</div>
<?php require IncPath . 'main/install.inc.php'; ?>
