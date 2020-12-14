<?php
require '../plug.php';
include 'html_headB.php';
?>

<?php
$seiten = new Pages ();
?>

<div id=wrapper>
<?php include 'sidebar_leftB.inc.php';?>
	<div id="mainwindow">
<?php
if ($page = $_GET ['page']) {
	if ($seiten->getTemplate ( $page ) ['module'] !== "empty")
		include 'forms/textpages.form.php';
} elseif (isset ( $_GET ['newpage'] )) {
	include 'forms/newpage.form.php';
} elseif ($_GET ['tool'] == "member") {
	include 'forms/users.form.php';
} elseif ($_GET ['tool'] == "profile") {
	if (isset ( $_GET ['pw'] )) {
		include 'forms/passw.form.php';
	} elseif (isset ( $_GET ['picture'] )) {
		include 'forms/profilepicture.form.php';
	} else {
		include 'forms/profile.form.php';
	}
} elseif ($_GET ['tool'] == "files") {
	include 'forms/files.form.php';
} elseif ($_GET ['tool'] == "templates") {
	include 'forms/templates.form.php';
} elseif ($system = $_GET ['system']) {
	include "forms/$system.form.php";
} else { // for custom modules
	foreach ( $menueOBJ->menueitem as $item ) {
		if ($_GET ['tool'] == $item->entry)
			include "forms/$item->entry.form.php";
	}
}
?>
</div>
	<!-- mainwindow -->
</div>
<!-- wrapper -->
<?php
include BasePath . 'html_foot.php';
?>