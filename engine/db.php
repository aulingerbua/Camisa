<?php
if (DBtype === "psql")
	$db = new Psqlo ( $host, $user, $port, $password, $database );
else
	$db = new mysqli ( $host, $user, $password, $database );

if ($db->connect_errno) {
	
	echo '<p class="alert">Datenbank Verbindungsfehler! ' . $db->connect_error . '</br>';
	echo 'Eventuell die Zugangsdaten prüfen.</p>';
	die ();
}
?>
