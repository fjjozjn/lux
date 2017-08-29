<?php 
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if( isset($_SESSION['upload_photo_add']) ){
	@unlink($pic_path_com.$_SESSION['upload_photo_add']);
	@unlink($pic_path_small.'s_'.$_SESSION['upload_photo_add']);
	@unlink($pic_path_small.'m_'.$_SESSION['upload_photo_add']);
	@unlink($pic_path_small.'l_'.$_SESSION['upload_photo_add']);
	unset($_SESSION['upload_photo_add']);
}
if( isset($_SESSION['upload_photo_mod']) ){
	@unlink($pic_path_com.$_SESSION['upload_photo_mod']);
	@unlink($pic_path_small.'s_'.$_SESSION['upload_photo_mod']);
	@unlink($pic_path_small.'m_'.$_SESSION['upload_photo_mod']);
	@unlink($pic_path_small.'l_'.$_SESSION['upload_photo_mod']);
	unset($_SESSION['upload_photo_mod']);
	//还要将photos值情况，否则modify是，$_SESSION['upload_photo_mod']又会被赋值
	if(isset($_GET['pid']) && $_GET['pid'] != ''){
		$mysql->q('update product set photos = ? where pid = ?', '', $_GET['pid']);	
	}
}



if( !isset($_SESSION['upload_photo_add']) && !isset($_SESSION['upload_photo_mod']) ){
	echo "<script>alert('Photo delete success.');history.go(-1);</script>";
	exit();	
}