<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
if(!defined('DEL_HTML'))define('DEL_HTML', 1);
//if(!defined('MAGIC_QUOTES_GPC'))define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

/**
   ----------------- SQL防注入頁 ----------------
change log

	2009.04.03 修改令DEL_HTML不與GPC開關關聯
	2009.04.06 修改，只進行HEL_HTML操作. escape操作已移至mysql class.
	2011.03.01	修改change log
	2011.06.15	加入了URL fb debug info

**/

// 過濾POST/GET/COOKIE中的< > ，防止XSS漏洞
function myaddslashes($value)
{
	if(is_array($value)){
		$value = array_map('myaddslashes', $value);
	}else{
		//if(!GPC_STATUS) $value = addslashes($value);
		//簡體字處理，UTF8下不需要
		//$string = str_replace('&amp;#', '&#', $string);
		if(DEL_HTML) $value = strip_tags($value);
	}
	return $value;
}
$_POST = array_map('myaddslashes', $_POST);
$_GET = array_map('myaddslashes', $_GET);
$_COOKIE = array_map('myaddslashes', $_COOKIE);

// 合併get post至request(之後就使用request)
// post中的數據會覆蓋get中的數據
$_REQUEST = array_merge($_GET, $_POST);

// 與File有關的操作與db無關，所以可以不過濾這項
// if($_FILES) $_FILES = array_map('myaddslashes', $_FILES);

// 過濾URL中的< > ，防止XSS漏洞
if(!empty($_SERVER['REQUEST_URI'])) {
	SHOW_ERROR && FB::info('URL: '. $_SERVER['REQUEST_URI']);
	
	$temp = urldecode($_SERVER['REQUEST_URI']);
	if(strpos($temp, '<') !== false || strpos($temp, '"') !== false)
		exit('Bad URL!');
	unset($temp);
}

