<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

/*
 * ajax引導頁
 *
 */

if($actArray[0] != 'ajax'){
	$act = 'ajax-' . $act;
}
if($model = modelExist2($act, true)){
	require($model);
}else{
	exit('閣下發起了未知操作，請返回首頁');
}
