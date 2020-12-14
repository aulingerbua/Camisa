<?php
get_header();
?>
<div id="wrapper">

	<div id="mainframe-2cols">
<?php
$seite = new Textpage ( $_GET ['page'] );
$seite->setOrderBy("chapter");

$inhalt = $seite->retrieveByPage ( TRUE );

for($i = 0; $i < count ( $inhalt ); $i ++) {
	echo "<article class='text-content transparent'>";
	echo '<h1>' . $inhalt [$i] ['title'] . '</h1>';
	echo $inhalt [$i] ['text'];
	echo "</article>";
}
?>	
</div>
	<!-- mainframe -->
</div>
<!-- wrapper -->
<?php 
include_javascript("extendArticle","template");
?>
<?php get_footer()?>