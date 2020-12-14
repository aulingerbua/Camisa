<?php
session_start ();

if (! $_SESSION ["grant"]) {
	// header('HTTP/1.1 403 Forbidden');
	header ( "LOCATION: " . HOME . "login?forbidden" );
	exit ();
}

if (! ($_SESSION ["grant"] === sslK () && $_SESSION ['level'] <= 2)) {
	// header('HTTP/1.1 403 Forbidden');
	header ( "LOCATION: " . HOME . "login?forbidden" );
	exit ();
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
<title><?=SITE?></title>
<meta charset="UTF-8">
<meta name="author" content="Armin Aulinger">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="backend/backendstyles.css">
<link rel="stylesheet" type="text/css" href="styles/filebrowser.css">
<script type="text/javascript"
	src="<?php echo Edsrc ?>tinymce/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
	height: 500,
	selector: "textarea#editor",
	language: "de",
	plugins: [
	          "autolink link image lists print preview code ",
	          "searchreplace wordcount fullscreen save table"
	    ],
	content_css: "styles/editor.css",
	menubar: "insert edit format table",
	toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | preview print fullscreen | code"
 });
</script>
</head>

<body>
