<div id="system-modules">
	<h3>Module</h3>
<?php
/*
 * handle remove request
 */
if ($_GET ['confirmed'] == "Abbrechen")
	unset ( $_GET ['remove'] );
elseif (($module = $_GET ['remove']) && $_GET ['confirmed'] == "OK") {
	if (file_exists ( ModulesPath . "/$module/remove.php" )) {
		include ModulesPath . "/$module/remove.php";
		if (! System::hasModule ( $module ))
			echo "<p>Module $module is not installed</p>";
		else {
			$success = RemoveModules::functions ( $module );
			$success = RemoveModules::tables ( $tables );
			$success = RemoveModules::styles ( $module );
			$success = RemoveModules::templates ( $templates );
			$success = RemoveModules::systemEntries ( $module );
			$success = RemoveModules::backendForm ( $backendForm );
			$success = RemoveModules::backendNavEntry ( $backendNav );
			if ($success)
				echo "<p>Module $module removed</p>";
			else
				echo "<p>Module $module could not be completely removed. Check the install log</p>";
		}
	} else
		echo "<p>no remove script</p>";
	unset ( $_GET ['remove'] );
}
if ($_GET ['remove']) :
	?>
<form action="" class="delete-entry" method="get">
		<p>Achtung! Diese Aktion löscht sämtliche Dateien und
			Datenbankeinträge des Moduls. Eventuell die Daten vorher sichern.</p>
		<input type="submit" name="confirmed" value="OK"> <input type="hidden"
			name="remove" value="<?=$_GET['remove']?>"> <input type="hidden"
			name="system" value="modules"> <input type="submit" name="confirmed"
			value="Abbrechen">
	</form>


<?php 
endif;
/*
 * handle install request
 */
if ($module = $_GET ['install']) {
	if (file_exists ( ModulesPath . "/$module/install.php" )) {
		include ModulesPath . "/$module/install.php";
		$inst = new Install ( $module, $version );
		if (! $inst->isInstalled ()) {
			if ($installTables)
				if (! $inst->installTables ()) {
					echo "<p class='alert'>Tables could not be installed.</p>";
					die ();
				}
			if ($functions)
				if (! $inst->installFunctions ( $functions )) {
					echo "<p class='alert'>Functions could not be installed.</p>";
					die ();
				}
			if ($AJAXscripts)
				if (! $inst->installAJAXscripts ( $AJAXscripts )) {
					echo "<p class='alert'>AJAXscripts could not be installed.</p>";
					die ();
				}
			if ($JavaScripts)
				if (! $inst->installJavaScripts ( $JavaScripts )) {
					echo "<p class='alert'>JavaScripts could not be installed.</p>";
					die ();
				}
			if ($installTemplates)
				if (! $inst->installTemplates ( $installTemplates )) {
					echo "<p class='alert'>Templates could not be installed.</p>";
					die ();
				}
			if ($installStyles)
				if (! $inst->installStyles ()) {
					echo "<p class='alert'>Styles could not be installed.</p>";
					die ();
				}
			if ($installBackendStyles)
				if (! $inst->installBackendStyles ()) {
					echo "<p class='alert'>Backend styles could not be installed.</p>";
					die ();
				}
			if ($installBackendNavEntry)
				if (! $inst->installBackendNavEntry ()) {
					echo "<p class='alert'>Backend navi entries could not be installed.</p>";
					die ();
				}
			if ($install_backend_form)
				if (! $inst->installBackendForms ( $install_backend_form )) {
					echo "<p class='alert'>Backend forms could not be installed.</p>";
					die ();
				}
			$inst->updateSystemTable ();
		} else
			echo "Module $module is already installed.";
	} else
		echo "<p>no install script</p>";
} elseif ($module = $_GET ['update']) {
	if (file_exists ( ModulesPath . "/$module/update.php" ))
		include ModulesPath . "/$module/update.php";
	else
		echo "<p>no update script</p>";
}
$installable_modules = dir ( ModulesPath );
while ( false !== ($module = $installable_modules->read ()) ) :
	if (is_dir ( ModulesPath . "$module" ) && $module != ".." && $module != ".") :
		?>
	<ul>
		<li><?=$module?></li>
		<li><ul>
				<li><a href="?system=modules&install=<?=$module?>">install</a></li>
				<li><a href="?system=modules&update=<?=$module?>">update</a></li>
				<li><a href="?system=modules&remove=<?=$module?>">remove</a></li>
			</ul></li>
	</ul>
<?php endif;?>

<?php endwhile;?>
</div>