<?php
include('../in7/db.php');
$db = mysql_connect($goDbInfo['host'],$goDbInfo['user'],$goDbInfo['passwd']) or die ("Unable to connect to Localhost");
mysql_select_db($goDbInfo['database'], $db) or die ("Could not select the database.");
