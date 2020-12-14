<?php
session_start();
header("Content-type: image/png");
// create empty image
$img = imagecreatetruecolor(130, 90);

// number of circles
$ncirc = mt_rand(2,6);
$_SESSION['ncirc'] = $ncirc;

for ($i=0; $i<$ncirc; $i++) {

	// color of circles
	$r = mt_rand(140,255);
	$g = mt_rand(140,255);
	$b = mt_rand(140,255);
	$color = imagecolorallocate($img, $r, $g, $b);
		
	// diameter and position
	$dm = mt_rand(5,60);
	$x = mt_rand(10,130);
	$y = mt_rand(10,80);
	imageellipse ($img, $x, $y, $dm, $dm, $color);
}
// render and destroy
imagepng($img);
imagedestroy($img);
?>

