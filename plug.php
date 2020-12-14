<?php

define("BasePath", dirname(__FILE__)."/");
include 'admin/settings.inc.php';
include 'engine/glob.php';
include 'engine/db.php';
include 'engine/functions.php';

spl_autoload_register('mainClasses');
?>