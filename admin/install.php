<?php
require '../plug.php';

require_once BasePath . 'html_head.php';
?>
<div class="camisa-logo">
	<img alt="CaMiSa banner" src="<?=ImagesPath?>Camisa_logo.svg">
</div>
<div class="install">
<?php
$inst = new Install ();
$log = start_install_log ();
//echo "installed? $in<br>";
if ($inst->isInstalled ()) {
	if ($inst->createAdmin () === FALSE)
		echo "<h2>System is already installed</h2>";
	elseif ($inst->createAdmin () === TRUE) {
		echo "<h2>System has been successfully installed</h2>";
		$log->write ( "The base system has been successfully installed", LOG_INFO );
	}
	echo '<a href="' . HOME . '">zur Startseite</a>';
} else {
	$log->write ( "Installing the base system.", LOG_INFO );
	if ($inst->installTables ()) {
		$inst->showAdminForm ();
		$inst->updateSystemTable ();
	}
}
?>
</div>
<!-- install -->
<?php
require_once BasePath . 'html_foot.php';
?>
