<?php
get_header();?>

<div id="wrapper">

<?php 
//include_once PagesPath . 'sidebar1.tpl.php';
?>
<article id="mainframe">
<?php 
	$seite = new Textpage('Home');
	
	$inhalt = $seite->retrieveByPage($textonly=TRUE)[0];
	
	echo '<h1>'.$inhalt['title'].'</h1>';
	echo $inhalt['text'];
?>	
</article><!-- outside -->
</div><!-- row -->
<?php get_footer()?>
