<?php
$sys = new System ();

$sys->dataBaseIo ( $sys->putSendmailSettings ( $_POST ) );

$settings = $sys->getSendmailSettings ();
$sys->sendmailSettings ($settings);
//$sys->sendmailSettings ( $sys->getSendmailSettings () );