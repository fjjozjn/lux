<?php
extract($_REQUEST);
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');

if(isSysAdmin()) {
    $row = $mysql->qone("select * from filemgr where id = ?", $del);
    unlink("files/$row[name]");
    $mysql->q("delete from filemgr where id = ?", $del);
}else{
    $row = $mysql->qone("select * from filemgr where id = ? and user_id = ?", $del, $_SESSION["logininfo"]["aID"]);
    unlink("files/$row[name]");
    $mysql->q("delete from filemgr where id = ? and user_id = ?", $del, $_SESSION["logininfo"]["aID"]);
}

header("Location:index.php");
