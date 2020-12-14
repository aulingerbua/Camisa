<?php
$menueJS = file_get_contents ( "menueItems.json" );
$menueOBJ = json_decode ( $menueJS );
?>
<nav id="sideB">
	<ul>
		<li><a href="<?php echo HOME ?>">Home</a></li>
		<?php if ( $_SESSION['level'] <= 1 ) :?>
		<li><a href="?tool=page" <?php make_current("tool", "page")?>>Seiten</a></li>
		<?php if ($_GET['tool']=="page") :?>
		<li>
			<ul>
				<li><a href="?tool=page&newpage">erstellen/ändern</a></li>
			<?php list_editable_pages()?>
			</ul>
		</li>			
			<?php endif;	endif;?>
		<li><a href="?tool=profile&profile=<?php echo $_SESSION ['user']?>"
			<?php make_current("tool", "profile")?>>Eigenes Profil</a></li>
			<?php if ($_GET['tool']=="profile") :?>
			<li>
			<ul>
				<li><a href="?tool=profile&picture">Profilbild</a></li>
				<li><a href="?tool=profile&pw">Passwort ändern</a></li>
			</ul>
		</li>
		<?php endif;?>
		<?php if ($_SESSION ['level'] < 0) : ?>
				<li><a href="?tool=member" <?php make_current("tool", "member")?>>Mitglieder</a></li>
		
			<?php if ($_GET ['tool'] == "member") :?>
			<li>
			<ul>
				<li><a href="?tool=member&member=new">Neu</a></li>
				<li><a href="?tool=member&member&all">Profile</a></li>
			</ul>
		</li>
			<?php endif;		endif;?>
		<?php
		// load menue items of custom modules
		foreach ( $menueOBJ->menueitem as $item ) {
			if ($_SESSION ['level'] <= $item->userlevel) {
				echo "<li><a href='?tool=$item->entry' " . set_current ( "tool", $item->entry ) . ">$item->title</a></li>\n";
				if ($subitem = $item->subitem) {
					echo "<li>\n<ul>";
					foreach ( $subitem as $sub ) {
						if ($_SESSION ['level'] <= $sub->userlevel) {
							if ($sub->function)
								call_user_func ( $sub->function );
							else
								echo "<li" . set_current ( $item->entry, $sub->entry ) . "><a href='?tool=$item->entry&$item->entry=$sub->entry'>$sub->title</a></li>\n";
						}
					}
					echo "</ul>\n</li>";
				}
			}
		}
		?>
		<?php if (current_user_level() <= 2) : ?>
				<li><a href="?tool=files"
			<?php make_current("tool", "files")?>>Files</a></li>
		<?php endif;?>
		<?php if ($_SESSION ['level'] < 0) : ?>
				<li><a href="?tool=templates"
			<?php make_current("tool", "templates")?>>Templates</a></li>
		<?php endif;?>
		<?php if ($_SESSION ['level'] < 0) : ?>
				<li><a href="?tool=system" <?php make_current("tool", "system")?>>System</a></li>
		
			<?php if ($_GET ['tool'] == "system") :?>
			<li>
			<ul>
				<li <?=$_GET['system'] ==  "update" ? " id='current'" : ""?>><a
					href="?tool=system&system=update">Systemupdate</a></li>
				<li <?=$_GET['system'] ==  "modules" ? " id='current'" : ""?>><a
					href="?tool=system&system=modules">Module</a></li>
				<li <?=$_GET['system'] ==  "settings" ? " id='current'" : ""?>><a
					href="?tool=system&system=settings">Settings</a></li>
				<li <?=$_GET['system'] ==  "logs" ? " id='current'" : ""?>><a
					href="?tool=system&system=logs">Logs</a></li>
			</ul>
		</li>
			<?php endif;	endif;?>
		<li><p><?php echo 'Angemeldet als '.$_SESSION['user'] ?></p></li>
		<li><p><?php echo '<a href=' . HOME . 'login?logout>abmelden</a>'?></p></li>
	</ul>
</nav>