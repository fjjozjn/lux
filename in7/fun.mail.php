<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
@todo				發送email
@param $to			發送到的email地址
@param $subject 	email標題
@param $body		郵件內容，或者包含郵件內容的文件地址(可以是php)
@param $more		更多的參數
@param $bodytype	$body所表示的郵箱內容類型： filepath（表示文件路徑） || html（表示html string）
*/
function sendingMail($to, $subject, $body, $more = false, $bodytype = 'filepath'){
	if(!checkEmail($to))return false;
	global $mailParams, $mailHeaders;
	if(is_array($more)){
		extract($more);
	}
	$mailHeaders['To']      = $to;
	$mailHeaders['Subject']	= mb_convert_encoding($subject, 'big5', 'utf-8');
	if($bodytype == 'filepath'){
		if(@file_exists($body)){
			ob_start();
			@require_once($body);
			@$body = ob_get_contents();
			ob_end_clean();
			if(empty($body)) return false;
		}else{
			return false;
		}
	}
	
	$mail_object =& Mail::factory('smtp', $mailParams);
	if ($mail_object->send($to, $mailHeaders, mb_convert_encoding($body, 'big5', 'utf-8'))){
		return 'ok';
	}else{
		return 'failed';
	}
}
?>