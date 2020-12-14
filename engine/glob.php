<?php
define ( "CamisaVersion", "0.9.5" );
define ( "SITE", $sitename );

define ( "IncPath", BasePath . "engine/modules/" );
define ( "JsPath", BasePath . "engine/js/" );
define ( "ajaxPath", BasePath . "engine/ajax/" );
define ( "dataPath", BasePath . "data/" );
define ( "Template", "$template/" );
define ( "SystemUpdatePath", BasePath . "admin/system/update/" );
define ( "ModulesPath", BasePath . "admin/modules/" );

define ( "PagesPath", BasePath . "templates/" . $template . "/" );
define ( "StylePath", "/templates/" . $template . "/" );

define ( "LogPath", BasePath . "admin/logs/" );

// server
$protocol = $_SERVER ['HTTPS'] == "on" ? "https" : "http";
define ( "URI", "$protocol://" . $_SERVER ['SERVER_NAME'] );
define ( "HOME", "/" );
define ( "Edsrc", HOME . "editor/" );
define ( "ImagesPath", "/img/" );
$loc = setlocale ( LC_ALL, 'de_DE', 'de_DE.UTF-8' );
date_default_timezone_set ( "Europe/Berlin" );
define ( "DBtype", $dbtype );
if (explode ( ".", phpversion () ) [0] == "7") {
	define ( "DBaccess", [ 
			'host' => $host,
			'user' => $user,
			'port' => $port,
			'password' => $password,
			'database' => $database 
	] );
} else {
	define ( "DBaccess", NULL );
	define ( "DBhost", $host );
	define ( "DBuser", $user );
	define ( "DBpassword", $password );
	define ( "DBname", $database );
	define ( "DBport", $port );
}

if ($debug === TRUE) {
	error_reporting ( E_ALL & ~ E_NOTICE );
	ini_set ( 'display_errors', 'On' );
} else {
	error_reporting ( - 1 );
	ini_set ( 'display_errors', 'Off' );
}

// function for registering main classes
function mainClasses($class) {
	require BasePath . 'engine/main/' . $class . '.inc.php';
}
?>