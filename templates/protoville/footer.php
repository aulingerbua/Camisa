<footer id="footer">
<?php
if (isset($_SESSION['user'])) {
	echo '<p >angemeldet als '.$_SESSION['user'].'<br />';
	echo '<a href="login?logout">abmelden</a></p>';
}
else {
	echo '<p><a href="login">anmelden</a></p>';
}
?>
<p><a href="Impressum.htm">Impressum</a></p>
</footer>
