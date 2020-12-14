<?php
get_header();
?>
<div id="wrapper">

<article id="mainframe">
<?php 
$seite = new Textpage($_GET['page']);
	
	$inhalt = $seite->retrieveByPage($textonly=TRUE)[0];
	
	echo '<h1>'.$inhalt['title'].'</h1>';
	echo $inhalt['text'];
?>	
</article><!-- mainframe -->
</div><!-- wrapper -->
<?php get_footer()?>