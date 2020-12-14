<?php $top = new PageMenue ();?>
<body>
	<header>
		<div id='site-header'>
		<img id="logo" alt="logo" src="/img/TestLogo.svg">
			<nav id="top-navi">
				<ul>
			<?php
			
			$top->createM ( $exclude = "Impressum" );
			if ($_SESSION ['grant'] === sslK () && $_SESSION ['level'] <= 2) :
				?>
			<li><a href="intern">Intern</a></li>
			<?php	endif; ?>
    </ul>
			</nav>
		</div>
		<script type="text/javascript" src="/templates/protoville/topNavi.js"></script>
	</header>