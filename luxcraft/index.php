<?php
// global中已對IP做判斷，所以無需設定調試標誌
//define('IS_DIST',		1);
//define('SHOW_ERROR',	1);

require('../in7/global.php');
require('./in38/global_admin.php');

// 需要特殊處理的入口操作
$specialList = array(
				'index',	//首頁
				'register', //用户填资料，发送邮件给管理员，然后由管理员注册				
				'login',	//登入
				'error',	//顯示致命的系統錯誤
				);

// 對 入口操作 作一些判斷與處理
if(!$act){
	$act = 'index';
}

if ($act == 'index' && isset($_SESSION['luxcraftlogininfo']) && is_array($_SESSION['luxcraftlogininfo'])){
	$act = 'main';
}

/*
2010-2-12
參考前台
使用了modelExist後，不再需要做入口合法性的判斷

$nomalList = array_merge($nomalList, $specialList);
if(!in_array($act, $nomalList) || $myerror->getError()){
	if(!$myerror->getError()){
		$myerror->error('閣下發起了未知操作，請返回重試', 'BACK');
	}
	$act = 'error';
}
*/
/*
var_dump($_SESSION['ftylogininfo']);
var_dump(SHOW_ERROR);
var_dump($ip);
*/
if($myerror->getError()){
	$act = 'error';
}else{
	$actArray = explode('-', $act);
}

// 載入Model頁
if(in_array($act, $specialList)){
	require_once('./special.php');
}elseif($actArray[0] == 'ajax' || $ajax){
	require_once('./ajax.php');
}else{
	require_once('./page.php');
}


?>