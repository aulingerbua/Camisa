<!DOCTYPE html>
<html lang="de">
<head>
<title><?=SITE?></title>
<meta charset="UTF-8">
<meta name="author" content="Armin Aulinger">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="/styles/styles.css">
<link rel="stylesheet" type="text/css" href="/filebrowser/styles.css">
<link rel="stylesheet" type="text/css" href="<?= StylePath ?>styles.css">
<?php load_module_styles()?>
<script type="text/javascript" src="editor/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
	height: 300,
	/*width: 800,*/
	selector: "textarea#post",
	language: "de",
	plugins: [
	          "lists link autolink"
	    ],
	content_css: "<?=StylePath."editor.css"?>",
	menubar: "insert edit format",
	toolbar: "undo redo | bold italic underline | bullist numlist | link"
 });
tinymce.init({
	height: 500,
	/*width: 800,*/
	selector: "textarea#editor",
	language: "de",
	plugins: [
	          "lists link autolink"
	    ],
	content_css: "<?=StylePath."editor.css"?>",
	menubar: "insert edit format",
	toolbar: "undo redo | bold italic underline | bullist numlist | link"
 });
</script>
</head>