<?php
echo "<div id='file-browser'></div>";?>
	<script src="filebrowser/browser.js"></script>
	<?php if (current_user_level() < 0) :?>
	<script type="text/javascript">
	initBrowser('storage','file-browser','all');
	</script>
	<?php else :?>
	<script type="text/javascript">
	initBrowser('storage/Medien','file-browser',['text','image']);
	</script>
	<?php endif;?>