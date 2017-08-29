<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();


if( isset($_GET['photo']) && $_GET['photo'] != '' && isset($_GET['bom_id']) && $_GET['bom_id'] != ''){
    @unlink($pic_path_fty_bom.$_GET['photo']);
    //@unlink($pic_path_small.'s_'.$_GET['photo']);
    //@unlink($pic_path_small.'m_'.$_GET['photo']);
    //@unlink($pic_path_small.'l_'.$_GET['photo']);
    //还要将表里的photos值清空，否则modify是，$_SESSION['upload_photo_mod']又会被赋值
    $rs = $mysql->q('update bom set photo = ? where id = ?', '', $_GET['bom_id']);
    if($rs === false){
        echo "<script>alert('Photo delete failure.(2)');history.go(-1);</script>";
    }else{
        echo "<script>alert('Photo delete success.');history.go(-1);</script>";
    }
}else{
    echo "<script>alert('Photo delete failure.(1)');history.go(-1);</script>";
}

exit();