<?php
if (file_exists ( SystemUpdatePath . "update.php" )) {
	include SystemUpdatePath . "update.php";
	$inst = new Update ( NULL, $version );
	if ($inst->isInstalled ())
		echo "<h1>System is up to date.</h1>";
	else {
		if ($updateClasses)
			$inst->updateClasses ();
		if ($updateForms)
			$inst->updateForms ();
		if ($queryFile)
			$inst->sourceQryFile ( SystemUpdatePath . "update.sql" );
			$inst->updateSystemTable("system was updated");
	}
} else
	echo "<h1>No update file found.</h1>";
?>