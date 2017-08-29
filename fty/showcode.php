<?php

session_start();
define('BEEN_INCLUDE', 1);
require_once('../class/My/Scode.php');

$fonts = array('ravie2.ttf', 'segoeprb.ttf');
			//'brlnsb.ttf', 'bernhc2.ttf' not good.

if(isset($_GET['whatfor']) && !empty($_GET['whatfor'])){
	$sessionname = $_GET['whatfor'];
}else{
	$sessionname = '';
}
$codeImg = new My_Scode($sessionname);
$codeImg->width = 180;
$codeImg->height = 60;
$codeImg->ttfFontFile = '../ui/font/'. $fonts[mt_rand(1, count($fonts)) - 1];		//字體
$codeImg->bgStLine = 1;							//背景交叉線的寬度
//$codeImg->bgImgColor = '#0000FF';				//背景顏色
$codeImg->bgMess = 4;							//噪音字數量
$codeImg->distort = 6;							//扭曲度
$codeImg->charset = 'upper';					//只顯示大寫字母數字(並且有所刪減)
$codeImg->bgStLineColor = '';					//背景交叉線的顏色，跟緊字體顏色

$codeImg->show();
