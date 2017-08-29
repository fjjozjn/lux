<?php

$saved_data = $_POST['text'];
session_start();
$_SESSION['saved_text'] = $saved_data;

exit();

?>