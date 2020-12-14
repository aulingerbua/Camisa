<?php $disp_log = $_GET['log'] ? $_GET['log'] : "install";?>
<nav class="tab">
	<ul>
		<li><a href="?tool=system&system=logs&log=install"
			<?php make_current("log", "install",true)?>>Install log</a></li>
		<li><a href="?tool=system&system=logs&log=system"
			<?php make_current("log", "system")?>>System log</a></li>
	</ul>
</nav>
<?php
if ($disp_log == "system")
	$log = start_system_log ();
else
	$log = start_install_log ();
	
$log->display ();
?>