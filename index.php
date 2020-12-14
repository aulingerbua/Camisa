<?php
// phpinfo(); CaMiSa
session_start ();

require_once 'plug.php';

/*
 * set the page to show if allowed,
 * by default show the home page
 */
$seitset = new Pages ();
$page = $_GET ["page"];

if ($page && ! $seitset->exist ( $page )) {
	//header ( "LOCATION: " . HOME . "errors/fileNotFound.php");
	header ($_SERVER["SERVER_PROTOCOL"]. " 404 Not Found" );
	exit ();
}

if ($seitset->visible ( $page, $_SESSION ['level'] ) === FALSE) {
	// header($_SERVER["SERVER_PROTOCOL"].'HTTP/1.1 403 Forbidden');
	header ( "LOCATION: " . HOME . "login?forbidden" );
	exit ();
}

include_once 'html_head.php';

// load the requested page
if ($page) {
	if ($tmpl = $seitset->getTemplate ( $page ) ['template']) {
		include_once PagesPath . $tmpl . '.tpl.php';
	} else {
		include_once PagesPath . 'default.tpl.php';
	}
} else {
	include_once PagesPath . 'home.tpl.php';
}

include_once 'html_foot.php';
?>

