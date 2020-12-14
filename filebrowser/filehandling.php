<?php
require_once '../plug.php';
require 'filehandling.inc.php';
$fileaction = new FileHandling ( BasePath );
//var_dump($_GET);
if ($_POST ['action'] === "delete") {
	if (! $fileaction->deleteFile ( $_POST ['dir'] ))
		echo "<span style='color:red'>$fileaction->err_msg</span>";
	exit ( 0 );
}

if ($_POST ['action'] === "mkdir") {
	if (! $fileaction->mkDir ( $_POST ['dir'] ))
		echo "<span style='color:red'>$fileaction->err_msg</span>";
	exit ( 0 );
}

if ($_GET ['action'] === "display") {
	//var_dump($_GET);
	if ($base_dir = $_GET ['dir']) {
		$contents = $fileaction->fetchDirectoryContents ( $base_dir );
		echo json_encode ( $contents );
	}
	exit ( 0 );
}

if ($_POST ['action'] === "upload") {
	$Mtypes = explode(",", $_POST ['types']);
	if (is_array($Mtypes))
		$fileaction->setMime ($Mtypes);
	
	if (! $_FILES)
		echo "<span style='color:red'>no files received.</span>";
	else {
		$file = key ( $_FILES );
		if (! $fileaction->upload ( $_POST ['tree'], $file ))
			echo "<span style='color:red'>$fileaction->err_msg</span>";
	}
}
?>
