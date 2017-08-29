<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();


if( isset($_GET['photo']) && $_GET['photo'] != '' && isset($_GET['pid']) && $_GET['pid'] != ''){
    @unlink($pic_path_com.$_GET['photo']);
    @unlink($pic_path_small.'s_'.$_GET['photo']);
    @unlink($pic_path_small.'m_'.$_GET['photo']);
    @unlink($pic_path_small.'l_'.$_GET['photo']);
    @unlink($pic_path_watermark.'l_water_'.$_GET['photo']);
    //还要将表里的photos值清空，否则modify是，$_SESSION['upload_photo_mod']又会被赋值
    $rs = $mysql->q('update product set photos = ? where pid = ?', '', $_GET['pid']);
    if($rs === false){
        echo "<script>alert('Photo delete failure.(2)');history.go(-1);</script>";
    }else{
        echo "<script>alert('Photo delete success.');history.go(-1);</script>";
    }
}elseif(isset($_GET['photo']) && $_GET['photo'] != '' && isset($_GET['poster_id']) && $_GET['poster_id'] != ''){
    @unlink($pic_path_com_poster.$_GET['photo']);
    @unlink($pic_path_small_poster.'s_'.$_GET['photo']);
    @unlink($pic_path_small_poster.'m_'.$_GET['photo']);
    @unlink($pic_path_small_poster.'l_'.$_GET['photo']);
    @unlink($pic_path_watermark.'l_water_'.$_GET['photo']);
    //还要将表里的photos值清空，否则modify是，$_SESSION['upload_photo_mod']又会被赋值
    $rs = $mysql->q('update poster set photo = ? where id = ?', '', $_GET['poster_id']);
    if($rs === false){
        echo "<script>alert('Photo delete failure.(2)');history.go(-1);</script>";
    }else{
        echo "<script>alert('Photo delete success.');history.go(-1);</script>";
    }
}else{
    echo "<script>alert('Photo delete failure.(1)');history.go(-1);</script>";
}

exit();