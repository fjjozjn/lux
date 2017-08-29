<?php
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

function checkAdminLogin(){	
	if (!isset($_SESSION['logininfo'])){
		//not login yet
		redirectTo('?act=login');
	}
}

function checkAdminPermission($perm, $ret = false){
	global $act, $myerror;
	$result = false;
	// echo "act : ".$act;
	// print_r_pre($_SESSION);
	
	if (checkAllPermission()){
		//this account has all permission 
		$result = true;
	}else{
		//this account doesn't have all permission
		$admin_perm = explode(',', $_SESSION["logininfo"]["aPerm"]);
		// print_r_pre($admin_perm);
		for ($i = 0 ; $i < count($admin_perm); $i++){
			if ($perm == $admin_perm[$i]){
				$result = true;
			}
		}
	}
	
	if (!$result && !$ret){		
		$myerror->error('阁下没有权限，请返回', 'main');
	}
	if ($ret){
		return $result;
	}
}


function checkAllPermission(){
	$result = false;
	$temp = substr($_SESSION["logininfo"]["aPerm"], 0, 2);
	if ($temp == PERM_ALL){
		$result = true;
	}
	return $result;
}


/*
 * 根据IP限制用户访问
 */
// function checkAdminIP($msg = '', $ipArray = false){
	// global $ip;
	// if(!$ipArray) $ipArray = $GLOBALS['TestUserIp'];
	// if(in_array($ip, $ipArray) ){
		// return true;
	// }else{
		// $GLOBALS['myerror']->error('阁下没有权限进入本系统，请返回'. ($msg ? '<br />'. $msg : ''), 'BACK');
		// return false;
	// }
// }

function checkAdminAllowedIP($msg=''){
	global $mysql, $ip, $myerror, $act;
	$info = $mysql->sp('CALL admin_check_tools_ip(?)', $ip);	
	$result =$mysql->fetch(0, 1);
	if (!$result){
		//not allowed this IP 	

		//if ($act == 'login' ||$act == 'index'){
		//	die("<div align='center'>YOU ARE NOT ALLOWED TO ACCESS THIS SYSTEM!!!</div>");
		//}else{
			$act = 'inside_error';
			$myerror->error('阁下没有权限进入本系统，请返回');
		//}
	}
	// echo 'CALL backend_list(0,100,"'.$temp_table.'", "'.$where_sql.'")';
	// print_r_pre($value_arr);
	
}

function redirectTo($link){	
	echo '<Script Language ="JavaScript">';
	echo "window.location.href = '".$link."';";
	echo "</Script>";	
}

/*
2010-06-09		添加了一个参数，令$removeApp true时，将在返回的游戏 array 中删除类型为 IS_APP (即应用程序而非游戏) 的资料。
*/
function getGameListByUserPerm($removeApp = false){
	global $gameList;
	$result = array();
	$gameperm = explode(',',$_SESSION['logininfo']['aGamePerm']);
	if ($gameperm[0] == '-1'){
		//all game permission
		if($removeApp){
			foreach($gameList as $row){
				if($row[3] == IS_APP){
					continue;
				}else{
					$result[] = $row;
				}
			}
		}else{
			$result = $gameList;
		}
	}else{
		//get game permission
		for($i = 0 ; $i < count($gameList); $i++){
			for ($j = 0 ; $j < count($gameperm); $j++){
				if ($gameList[$i][1] == $gameperm[$j]){
					$result[] = $gameList[$i];
					break;
				}
			}
		}	
	}
	return $result;
}

function getGameServerName($ser_arr, $value){
	for($i =0 ;$i < count($ser_arr); $i++){
		if ($ser_arr[$i][1] == $value){
			return $ser_arr[$i][0];
		}
	}
	return " - ";
}

function getDiffDate($start, $end){
	//please note that DO NOT contain time in date string
	$s = strtotime($start);
	$e = strtotime($end);
	
	return ($e-$s)/86400;
}

function getAddDate($ori_date, $days){
	$temp = strtotime($ori_date);
	// echo $temp;
	$result = $temp + ($days * 86400);
	return date('Y-m-d', $result);

}	

// function getTitleByFromSelect($input_arr, $value){
	// $result = "";
	// for($i = 0 ; $i < count($input_arr); $i++){
		// if ($input_arr[$i][1] == $value){
			// $result = $input_arr[$i][0];
			// break;
		// }
	// }
	// return $result;
// }










//按比例縮小圖片
function getimgsize($oldwidth,$oldheight,$imgwidth,$imgheight)
{
	//$oldwidth設置的寬度，$oldheight設置的高度，$imgwidth圖片的寬度，$imgheight圖片的高度

	//單元格裝得進圖片，則按圖片的真實大小顯示
	if($imgwidth <= $oldwidth && $imgheight <= $oldheight)
	{
		$arraysize = array('width' => $imgwidth, 'height' => $imgheight);
		return $arraysize;
	}
	else
	{
		$suoxiaowidth = $imgwidth - $oldwidth;
		$suoxiaoheight = $imgheight - $oldheight;
		$suoxiaoheightper = $suoxiaoheight / $imgheight;
		$suoxiaowidthper = $suoxiaowidth / $imgwidth;
		if($suoxiaoheightper >= $suoxiaowidthper)
		{
			//單元格高度為準
			$aftersuoxiaowidth = $imgwidth * (1 - $suoxiaoheightper);
			$arraysize = array('width' => $aftersuoxiaowidth, 'height' => $oldheight);
			return $arraysize;
		}
		else
		{
			//單元格寬度為準
			$aftersuoxiaoheight = $imgheight * (1 - $suoxiaowidthper);
			$arraysize = array('width' => $oldwidth, 'height' => $aftersuoxiaoheight);
			return $arraysize;
		}
	}
} 

//格式化货币
function fmoney($num) {
	$num=0+$num;
	$num = sprintf("%.02f",$num);
	if(strlen($num) <= 6) return $num;
	//从最后开始算起，每3个数它加一个","
	for($i=strlen($num)-1,$k=1, $j=100; $i >= 0; $i--,$k++) {
		$one_num = substr($num,$i,1);
		if($one_num ==".") {
			$numArray[$j--] = $one_num;
			$k=0;
			continue;
		}

		if($k%3==0 and $i!=0) {
			//如果正好只剩下3个数字，则不加','
			$numArray[$j--] = $one_num;
			$numArray[$j--] = ",";
			$k=0;
		} else {
			$numArray[$j--]=$one_num;
		}
	}
	ksort($numArray);
	return join("",$numArray);
}


function umoney($num,$type="usd") {
	global $numTable,$commaTable,$moneyType;

	//global $numTable;
	$numTable[0]="ZERO ";
	$numTable[1]="ONE ";
	$numTable[2]="TWO ";
	$numTable[3]="THREE ";
	$numTable[4]="FOUR ";
	$numTable[5]="FIVE ";
	$numTable[6]="SIX ";
	$numTable[7]="SEVEN ";
	$numTable[8]="EIGHT ";
	$numTable[9]="NINE ";
	$numTable[10]="TEN ";
	$numTable[11]="ELEVEN ";
	$numTable[12]="TWELVE ";
	$numTable[13]="THIRTEEN ";
	$numTable[14]="FOURTEEN ";
	$numTable[15]="FIFTEEN ";
	$numTable[16]="SIXTEEN ";
	$numTable[17]="SEVENTEEN ";
	$numTable[18]="EIGHTEEN ";
	$numTable[19]="NINETEEN ";
	$numTable[20]="TWENTY ";
	$numTable[30]="THIRTY ";
	$numTable[40]="FORTY ";
	$numTable[50]="FIFTY ";
	$numTable[60]="SIXTY ";
	$numTable[70]="SEVENTY ";
	$numTable[80]="EIGHTY ";
	$numTable[90]="NINETY ";
	
	$commaTable[0]="HUNDRED ";
	$commaTable[1]="THOUSAND ";
	$commaTable[2]="MILLION ";
	$commaTable[3]="MILLIARD ";
	$commaTable[4]="BILLION ";
	$commaTable[5]="????? ";
	
	//单位
	//$moneyType["usd"]="DOLLARS ";
	//应要求把DOLLARS去掉了
	$moneyType["usd"]="";
	//$moneyType["usd_1"]="CENTS ONLY";
	//应 kevin 要求把CENTS放在前面
	$moneyType["usd_1"]="CENTS ";
	
	$moneyType["usd_2"]="ONLY";
	$moneyType["rmb"]="YUAN ";
	$moneyType["rmb_1"]="FEN ONLY";


	if($type=="") $type="usd";
	$fnum = fmoney($num);
	$numArray = explode(",",$fnum);
	$resultArray = array();
	$k=0;
	$cc=count($numArray);
	for($i = 0; $i < count($numArray); $i++) {
		$num_str = $numArray[$i];
		//echo "<br>";
		//小数位的处理400.21
		if(eregi("\.",$num_str)) {
			$dotArray = explode(".",$num_str);
			if($dotArray[1] != 0) {
				$resultArray[$k++]=format3num($dotArray[0]+0);
				$resultArray[$k++]=$moneyType[strtolower($type)];
				$resultArray[$k++]="AND ";
				$resultArray[$k++]=$moneyType[strtolower($type)."_1"];
				$resultArray[$k++]=format3num($dotArray[1]+0);
				$resultArray[$k++]=$moneyType[strtolower($type)."_2"];
			} else {
				$resultArray[$k++]=format3num($dotArray[0]+0);
				$resultArray[$k++]=$moneyType[strtolower($type)];
				$resultArray[$k++]=$moneyType[strtolower($type)."_2"];
			}
		} else {
			//非小数位的处理
			if(($num_str+0)!=0) {
				$resultArray[$k++]=format3num($num_str+0);
				$resultArray[$k++]=$commaTable[--$cc];
				//判断：除小数外其余若不为零则加and
				for($j=$i; $j <= $cc; $j++) {
					//echo "<br>";
					//echo $numArray[$j];
					if($numArray[$j] !=0) {
						$resultArray[$k++]="AND ";
						break;
					}
				}
			}
		}
	}
	return join("",$resultArray);
}


function format3num($num) {
	global $numTable,$commaTable;
	$numlen = strlen($num);
	for($i = 0,$j = 0;$i < $numlen; $i++) {
		$bitenum[$j++] = substr($num,$i,1);
	}
	if($num==0) return "";
	if($numlen == 1) return $numTable[$num];
	if($numlen == 2) {
		if($num <= 20) return $numTable[$num];
		//第一位不可能零
		if($bitenum[1]==0) {
			return $numTable[$num];
		} else {
			return trim($numTable[$bitenum[0]*10])."-".$numTable[$bitenum[1]];
		}
	}
	//第一个不可能为零
	if($numlen == 3) {
		if($bitenum[1]==0 && $bitenum[2]==0) {
			//100
			return $numTable[$bitenum[0]].$commaTable[0];
		} elseif($bitenum[1]==0) {
			//102
			return $numTable[$bitenum[0]].$commaTable[0].$numTable[$bitenum[2]];
		} elseif ($bitenum[2]==0) {
			//120
			return $numTable[$bitenum[0]].$commaTable[0].$numTable[$bitenum[1]*10];
		} else {
			//123
			//zjn modify 这里加了个判断，否则11会输出成 TEM-ONE
			//bitenum[1]代表十位，为1是作特殊处理 
			if($bitenum[1] == 1){
				return $numTable[$bitenum[0]].$commaTable[0].($numTable[$bitenum[1]*10+$bitenum[2]]);
			}else{
				return $numTable[$bitenum[0]].$commaTable[0].trim($numTable[$bitenum[1]*10])."-".$numTable[$bitenum[2]];
			}
		}
	}
	return $num;
}

//格式化显示钱：保留两位小数，不足的补0，整数部分每三位逗号隔开
function formatMoney($money){
	return fmoney(sprintf("%01.2f", round(floatval($money), 2)));
}

//20130812 数字中有逗号不方便提交，所以只保留两位小数，不需要逗号分隔
function my_formatMoney($money){
    return sprintf("%01.2f", round(floatval($money), 2));
}

//将参数中的逗号去掉的加法
function myAdd($a, $b){
	$a = str_replace(',', '', $a);
	$b = str_replace(',', '', $b);	
	return $a+$b;
}

//将参数中的逗号去掉的加法
function mySub($a, $b){
	$a = str_replace(',', '', $a);
	$b = str_replace(',', '', $b);	
	return $a-$b;
}

//将参数中的逗号去掉的减法
function myMul($a, $b){
	$a = str_replace(',', '', $a);
	$b = str_replace(',', '', $b);	
	return $a*$b;
}

//組合send_to的內容，公司名+地址
/*function combineSendTo($cid, $sid, $contact_address){
	$send_to = '';
	if($cid != '' && $sid == ''){
		$rtn = mysql_qone('select name from customer where cid = ?', $cid);
	}elseif($cid == '' && $sid != ''){
		$rtn = mysql_qone('select name from supplier where sid = ?', $sid);	
	}
	if($rtn){
		$send_to = $rtn['name'] . "\r\n" . $contact_address;

		//$rtn = mysql_qone('select address from contact where name = ?', $contact_name);
		//if($rtn){
		//	$send_to .= $rtn['address'];
		//}

		return $send_to;
	}
	return 'none';
}*/

//php對ajax傳來的中文escape後的信息進行解碼
function unescape($str){
	$ret = '';
	$len = strlen($str);
	for ($i = 0; $i < $len; $i++){
		if ($str[$i] == '%' && $str[$i+1] == 'u'){
			$val = hexdec(substr($str, $i+2, 4));
			if ($val < 0x7f) $ret .= chr($val);
			else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
			else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
			$i += 5;
		}
		else if ($str[$i] == '%'){
			$ret .= urldecode(substr($str, $i, 3));
			$i += 2;
		}
		else $ret .= $str[$i];
	}
	return $ret;
} 

//壓縮圖片
function makethumb($srcFile, $dstFile, $type=NULL, $markwords=NULL,$markimage=NULL){
	global $thumb_width, $thumb_height, $thumb_quality;
	$data = getimagesize($srcFile);
    switch($data[2])
	{
		case 1:
			$im=imagecreatefromgif($srcFile);
			break;
		case 2:
			$im=imagecreatefromjpeg($srcFile);
			break;
		case 3:
			$im=imagecreatefrompng($srcFile);
			break;
	}
	if(!$im) return false;
	$srcW=imagesx($im);
	$srcH=imagesy($im);

	$dstX=$dstY=$fPosW=$fPosH=0;
	if($type){
		$dstH = $thumb_height[$type];
		$dstW = $thumb_width[$type];
		if($dstH == 0){
			$fdstH = $dstH = round($srcH*$dstW/$srcW);
			$fdstW = $dstW;
		}elseif($dstW == 0){
			$fdstW = $dstW = round($srcW*$dstH/$srcH);
			$fdstH = $dstH;
		}else{
			if($srcW>$srcH){
				if($srcW / $srcH < $dstW / $dstH){
					$fdstW = round($srcW*$dstH/$srcH);
					$fdstH = $dstH;
				}else{
					$fdstH = round($srcH*$dstW/$srcW);
					$fdstW = $dstW;
				}
			}else{
				$fdstW = round($srcW*$dstH/$srcH);
				$fdstH = $dstH;
			}
			$fPosW = floor(($dstW - $fdstW) / 2);
			$fPosH = floor(($dstH - $fdstH) / 2);
		}
	}else{
		$fdstH = $dstH = $srcH;
		$fdstW = $dstW = $srcW;
	}
	
	$ni=imagecreatetruecolor($dstW,$dstH);
	if($dstH > 0 && $dstW > 0 && $type){
		$white = imagecolorallocate($ni,255,255,255);
		//$black = imagecolorallocate($ni,0,0,0);
		imagefilledrectangle($ni,0,0,$dstW,$dstH,$white); 
	}

    //20150119 加水印，不过有时有有时没有，不知道为什么
    /*if($markwords){
        $black = imagecolorallocate($ni, 200, 170, 200);
        ImageTTFText($ni, 10, 0, 20, 10, $black, ROOT_DIR.'font/arial.ttf', $markwords);
    }*/

	imagecopyresized($ni,$im,$fPosW,$fPosH,0,0,$fdstW,$fdstH,$srcW,$srcH);
	switch($data[2])
	{
		case 1:
			imagegif($ni,$dstFile);
			break;
		case 2:
			imagejpeg($ni,$dstFile, $thumb_quality);
			break;
		case 3:
			imagepng($ni,$dstFile);
			break;
	}
	imagedestroy($im);
	imagedestroy($ni);
}


/**
 * Text Watermark Point:
 *   #1   #2    #3
 *   #4   #5    #6
 *   #7   #8    #9
 */

/**
 * 给图片添加文字水印 可控制位置，旋转，多行文字    **有效字体未验证**
 * @param string $imgurl  图片地址
 * @param array $text   水印文字（多行以'|'分割）
 * @param int $fontSize 字体大小
 * @param type $color 字体颜色  如： 255,255,255
 * @param int $point 水印位置
 * @param type $font 字体
 * @param int $angle 旋转角度  允许值：  0-90   270-360 不含
 * @param string $newimgurl  新图片地址 默认使用后缀命名图片
 * @return boolean
 */
function createWordsWatermark($imgurl, $text, $fontSize = '14', $color = '0,0,0', $point = '1', $font = '', $angle = 0, $newimgurl = '') {

    $imageCreateFunArr = array('image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/gif' => 'imagecreatefromgif');
    $imageOutputFunArr = array('image/jpeg' => 'imagejpeg', 'image/png' => 'imagepng', 'image/gif' => 'imagegif');

//获取图片的mime类型
    $imgsize = getimagesize($imgurl);

    if (empty($imgsize)) {
        return false; //not image
    }

    $imgWidth = $imgsize[0];
    $imgHeight = $imgsize[1];
    $imgMime = $imgsize['mime'];

    if (!isset($imageCreateFunArr[$imgMime])) {
        return false; //do not have create img function
    }
    if (!isset($imageOutputFunArr[$imgMime])) {
        return false; //do not have output img function
    }

    $imageCreateFun = $imageCreateFunArr[$imgMime];
    $imageOutputFun = $imageOutputFunArr[$imgMime];

    $im = $imageCreateFun($imgurl);

    /*
     * 参数判断
     */
    $color = explode(',', $color);
    $text_color = imagecolorallocate($im, intval($color[0]), intval($color[1]), intval($color[2])); //文字水印颜色
    $point = intval($point) > 0 && intval($point) < 10 ? intval($point) : 1; //文字水印所在的位置
    $fontSize = intval($fontSize) > 0 ? intval($fontSize) : 14;
    $angle = ($angle >= 0 && $angle < 90 || $angle > 270 && $angle < 360) ? $angle : 0; //判断输入的angle值有效性
    $fontUrl = $font; //有效字体未验证
    $text = explode('|', $text);
    $newimgurl = $newimgurl ? $newimgurl : $imgurl . '_WordsWatermark.jpg'; //新图片地址 统一图片后缀

    /**
     *  根据文字所在图片的位置方向，计算文字的坐标
     * 首先获取文字的宽，高， 写一行文字，超出图片后是不显示的
     */
    $textLength = count($text) - 1;
    $maxtext = 0;
    foreach ($text as $val) {
        $maxtext = strlen($val) > strlen($maxtext) ? $val : $maxtext;
    }
    $textSize = imagettfbbox($fontSize, 0, $fontUrl, $maxtext);
    $textWidth = $textSize[2] - $textSize[1]; //文字的最大宽度
    $textHeight = $textSize[1] - $textSize[7]; //文字的高度
    $lineHeight = $textHeight + 3; //文字的行高
//是否可以添加文字水印 只有图片的可以容纳文字水印时才添加
    if ($textWidth + 40 > $imgWidth || $lineHeight * $textLength + 40 > $imgHeight) {
        return false; //图片太小了，无法添加文字水印
    }

    if ($point == 1) { //左上角
        $porintLeft = 20;
        $pointTop = 20;
    } elseif ($point == 2) { //上中部
        $porintLeft = floor(($imgWidth - $textWidth) / 2);
        $pointTop = 20;
    } elseif ($point == 3) { //右上部
        $porintLeft = $imgWidth - $textWidth - 20;
        $pointTop = 20;
    } elseif ($point == 4) { //左中部
        $porintLeft = 20;
        $pointTop = floor(($imgHeight - $textLength * $lineHeight) / 2);
    } elseif ($point == 5) { //正中部 高度2/3
        $porintLeft = floor(($imgWidth - $textWidth) / 2);
        $pointTop = floor(($imgHeight - $textLength * $lineHeight) * 2 / 3);
    } elseif ($point == 6) { //右中部
        $porintLeft = $imgWidth - $textWidth - 20;
        $pointTop = floor(($imgHeight - $textLength * $lineHeight) / 2);
    } elseif ($point == 7) { //左下部
        $porintLeft = 20;
        $pointTop = $imgHeight - $textLength * $lineHeight - 20;
    } elseif ($point == 8) { //中下部
        $porintLeft = floor(($imgWidth - $textWidth) / 2);
        $pointTop = $imgHeight - $textLength * $lineHeight - 20;
    } elseif ($point == 9) { //右下部
        $porintLeft = $imgWidth - $textWidth - 20;
        $pointTop = $imgHeight - $textLength * $lineHeight - 20;
    }

//如果有angle旋转角度，则重新设置 top ,left 坐标值
    if ($angle != 0) {
        if ($angle < 90) {
            $diffTop = ceil(sin($angle * M_PI / 180) * $textWidth);

            if (in_array($point, array(1, 2, 3))) {// 上部 top 值增加
                $pointTop += $diffTop;
            } elseif (in_array($point, array(4, 5, 6))) {// 中部 top 值根据图片总高判断
                if ($textWidth > ceil($imgHeight / 2)) {
                    $pointTop += ceil(($textWidth - $imgHeight / 2) / 2);
                }
            }
        } elseif ($angle > 270) {
            $diffTop = ceil(sin((360 - $angle) * M_PI / 180) * $textWidth);

            if (in_array($point, array(7, 8, 9))) {// 上部 top 值增加
                $pointTop -= $diffTop;
            } elseif (in_array($point, array(4, 5, 6))) {// 中部 top 值根据图片总高判断
                if ($textWidth > ceil($imgHeight / 2)) {
                    $pointTop = ceil(($imgHeight - $diffTop) / 2);
                }
            }
        }
    }

    foreach ($text as $key => $val) {
        imagettftext($im, $fontSize, $angle, $porintLeft, $pointTop + $key * $lineHeight, $text_color, $fontUrl, $val);
    }

// 输出图像
    $imageOutputFun($im, $newimgurl, 80);

// 释放内存
    imagedestroy($im);
    return $newimgurl;
}



//2012.3.27 用户只能浏览自己创建的内容或浏览与自己在同一group的其他用户创建的内容
function judgeUserPerm($modid){
	global $act, $myerror;
	$sql = '';
	if(!isSysAdmin()){
		
		if(strpos($act, "modifyquotation") !== false){
			$sql = 'select created_by as printed_by from quotation where qid = ?';
		}elseif(strpos($act, "modifyproforma") !== false){
			$sql = 'select printed_by from proforma where pvid = ?';
		}elseif(strpos($act, "modifyinvoice") !== false){
			$sql = 'select printed_by from invoice where vid = ?';
		}elseif(strpos($act, "modifycustomsinvoice") !== false){
			$sql = 'select printed_by from customs_invoice where vid = ?';
		}elseif(strpos($act, "modifypurchase") !== false){
			// as printed_by 是为了下面好统一处理
			$sql = 'select created_by as printed_by from purchase where pcid = ?';
		}elseif(strpos($act, "modifycustomer") !== false){
			$sql = 'select created_by as printed_by from customer where cid = ?';
		}elseif(strpos($act, "modifycontact") !== false){
			$sql = 'select created_by as printed_by from customer where cid in (select cid from contact where id = ?)';
		}elseif(strpos($act, "warehouse_item") !== false){
            $sql = 'select created_by as printed_by from warehouse_item_unique where id = ?';
        }
		
		if($sql != ''){
			$rtn = mysql_qone("$sql", $modid);
			if(strpos($act, "modifypurchase") !== false){
				$rtn_purchase = mysql_qone('select AdminName from tw_admin where AdminNameChi = ?', $rtn['printed_by']);
				$rtn['printed_by'] = $rtn_purchase['AdminName'];
			}
			if($rtn['printed_by'] != $_SESSION['logininfo']['aName']){
				$rtn1 = mysql_qone('select AdminLuxGroup from tw_admin where AdminName = ?', $rtn['printed_by']);
				$rtn2 = mysql_qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
				if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
					$myerror->error('Without Permission To Access', 'main');
				}
			}
		}
	}
}

//2012.7.23 用户只能浏览自己创建的内容或浏览group中存在自己名字的用户创建的内容
function judgeUserPermNew($modid){
    global $act, $myerror;
    $sql = '';
    if(!isSysAdmin()){

        if(strpos($act, "modifyquotation") !== false){
            $sql = 'select created_by as printed_by from quotation where qid = ?';
        }elseif(strpos($act, "modifyproforma") !== false){
            $sql = 'select printed_by from proforma where pvid = ?';
        }elseif(strpos($act, "modifyinvoice") !== false){
            $sql = 'select printed_by from invoice where vid = ?';
        }elseif(strpos($act, "modifycustomsinvoice") !== false){
            $sql = 'select printed_by from customs_invoice where vid = ?';
        }elseif(strpos($act, "modifypurchase") !== false){
            // as printed_by 是为了下面好统一处理
            $sql = 'select created_by as printed_by from purchase where pcid = ?';
        }elseif(strpos($act, "modifycustomer") !== false){
            $sql = 'select created_by as printed_by from customer where cid = ?';
        }elseif(strpos($act, "modifycontact") !== false){
            $sql = 'select created_by as printed_by from customer where cid in (select cid from contact where id = ?)';
        }elseif(strpos($act, "modifysample_order") !== false){
            $sql = 'select created_by as printed_by from sample_order where so_no = ?';
        }elseif(strpos($act, "modify_payment_new") !== false){
            $sql = 'select remitter from payment_new where py_no = ?';
        }

        if($sql != ''){
            $rtn = mysql_qone("$sql", $modid);

            if(strpos($act, "modifypurchase") !== false){
                $rtn_purchase = mysql_qone('select AdminName from tw_admin where AdminNameChi = ?', $rtn['printed_by']);
                $rtn['printed_by'] = $rtn_purchase['AdminName'];
            }
            //20130916
            if(strpos($act, "modify_payment_new") !== false){
                $rtn_payment_new = mysql_qone('select created_by from customer where cid = ?', $rtn['remitter']);
                $rtn['printed_by'] = $rtn_payment_new['created_by'];
            }
            if($rtn['printed_by'] != $_SESSION['logininfo']['aName']){
                if(!judgeUserPermGroup($rtn['printed_by'])){
                    $myerror->error('Without Permission To Access', 'main');
                }
            }
        }
    }
}

//20160824 统一判断
function judgeUserPermGroup($user)
{
    $rtn = mysql_qone('select AdminLuxGroup from tw_admin where AdminName = ? or AdminNameChi = ?', $user, $user);
    if(strpos($rtn['AdminLuxGroup'], $_SESSION['logininfo']['aName']) === false){
        return false;
    }
    return true;
}


//2012.4.2 自动生成 proforma invoice 和 purchase 的 ID
function autoGenerationID(){
	global $act;
	$sql = '';
	
	$rtn = mysql_qone('select * from setting');
	
	if(strpos($act, "sample_order") !== false){	//add 要 ， modify 的 copy 也要
		$sql = 'select so_no from sample_order order by so_no desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['so_no'] == ''){
			if($result['so_no']){
				return substr($result['so_no'], 0, 3).sprintf("%07d", substr($result['so_no'], 3)+1);
			}else{
				return 'SO00000001';
			}
		}else{
			if(substr($rtn['so_no'], 0 ,3) == substr($result['so_no'], 0, 3)){
				if(substr($rtn['so_no'], 3) > substr($result['so_no'], 3)){
					return $rtn['so_no'];
				}else{
					return substr($result['so_no'], 0, 3).sprintf("%07d", substr($result['so_no'], 3)+1);
				}	
			}
			else{
				return $rtn['so_no'];
			}
		}
	}elseif(strpos($act, "addquotation") !== false){
		$sql = 'select qid from quotation order by qid desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['qid'] == ''){
			if($result['qid']){
				return substr($result['qid'], 0, 3).sprintf("%07d", substr($result['qid'], 3)+1);
			}else{
				return 'QT00000001';
			}
		}else{
			if(substr($rtn['qid'], 0 ,3) == substr($result['qid'], 0, 3)){
				if(substr($rtn['qid'], 3) > substr($result['qid'], 3)){
					return $rtn['qid'];
				}else{
					return substr($result['qid'], 0, 3).sprintf("%07d", substr($result['qid'], 3)+1);
				}	
			}
			else{
				return $rtn['qid'];
			}
		}
	}elseif(strpos($act, "proforma") !== false){
		$sql = 'select pvid from proforma order by pvid desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['pvid'] == ''){
			//setting 为空，则用数据库中最大的那个加1
			if($result['pvid']){
				return substr($result['pvid'], 0, 3).sprintf("%07d", substr($result['pvid'], 3)+1);
			}else{
				return 'PI00000001';	
			}
		}else{
			//前缀只取前 三位（不取前两位是以防以后会改为三位的前缀） ，！！！如果以后前缀位数有变则要改代码
			//前缀相同情况
			if(substr($rtn['pvid'], 0 ,3) == substr($result['pvid'], 0, 3)){
				if(substr($rtn['pvid'], 3) > substr($result['pvid'], 3)){
					//前缀相同，setting的序号大则用setting的
					return $rtn['pvid'];
				}else{
					//前缀相同，setting的序号小或等则用数据库中最大的那个加1
					return substr($result['pvid'], 0, 3).sprintf("%07d", substr($result['pvid'], 3)+1);
				}	
			}
			//前缀不同情况
			else{
				//前缀不同，则用新的
				return $rtn['pvid'];
			}
		}
	}elseif(strpos($act, "addinvoice") !== false){
		$sql = 'select vid from invoice order by vid desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['vid'] == ''){
			if($result['vid']){
				return substr($result['vid'], 0, 3).sprintf("%07d", substr($result['vid'], 3)+1);
			}else{
				return 'PI00000001';
			}
		}else{
			if(substr($rtn['vid'], 0 ,3) == substr($result['vid'], 0, 3)){
				if(substr($rtn['vid'], 3) > substr($result['vid'], 3)){
					return $rtn['vid'];
				}else{
					return substr($result['vid'], 0, 3).sprintf("%07d", substr($result['vid'], 3)+1);
				}	
			}
			else{
				return $rtn['vid'];
			}
		}
	}elseif(strpos($act, "addcustomsinvoice") !== false){
		$sql = 'select vid from customs_invoice order by vid desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['cid'] == ''){
			return substr($result['vid'], 0, 3).sprintf("%07d", substr($result['vid'], 3)+1);
		}else{
			if(substr($rtn['cid'], 0 ,3) == substr($result['vid'], 0, 3)){
				if(substr($rtn['cid'], 3) > substr($result['vid'], 3)){
					return $rtn['cid'];
				}else{
					return substr($result['vid'], 0, 3).sprintf("%07d", substr($result['vid'], 3)+1);
				}	
			}
			else{
				return $rtn['cid'];
			}
		}
	//PI转为fty PO也需要自动生成NO.	
	}elseif(strpos($act, "addpurchase") !== false || strpos($act, "modifypurchase") !== false){
		$sql = 'select pcid from purchase order by pcid desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['pcid'] == ''){
			if($result['pcid']){
				return substr($result['pcid'], 0, 3).sprintf("%07d", substr($result['pcid'], 3)+1);
			}else{
				return 'PO00000001';
			}
		}else{
			if(substr($rtn['pcid'], 0 ,3) == substr($result['pcid'], 0, 3)){
				if(substr($rtn['pcid'], 3) > substr($result['pcid'], 3)){
					return $rtn['pcid'];
				}else{
					return substr($result['pcid'], 0, 3).sprintf("%07d", substr($result['pcid'], 3)+1);
				}	
			}
			else{
				return $rtn['pcid'];
			}
		}
	}elseif(strpos($act, "sendform") !== false){
		$sql = 'select g_id from bom order by g_id desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['bomid'] == ''){
			return substr($result['g_id'], 0, 3).sprintf("%07d", substr($result['g_id'], 3)+1);
		}else{
			if(substr($rtn['bomid'], 0 ,3) == substr($result['g_id'], 0, 3)){
				if(substr($rtn['bomid'], 3) > substr($result['g_id'], 3)){
					return $rtn['bomid'];
				}else{
					return substr($result['g_id'], 0, 3).sprintf("%07d", substr($result['g_id'], 3)+1);
				}	
			}
			else{
				return $rtn['bomid'];
			}
		}		
	}elseif(strpos($act, "invoice_to_packing_list") !== false || strpos($act, "delivery_to_packing_list") !== false){
		$sql = 'select pl_id from packing_list order by pl_id desc limit 1';
		$result = mysql_qone($sql);
		if($rtn['pl_id'] == ''){
			if($result['pl_id']){
				return substr($result['pl_id'], 0, 3).sprintf("%07d", substr($result['pl_id'], 3)+1);
			}else{
				return 'PL00000001';
			}
		}else{
			if(substr($rtn['pl_id'], 0 ,3) == substr($result['pl_id'], 0, 3)){
				if(substr($rtn['pl_id'], 3) > substr($result['pl_id'], 3)){
					return $rtn['pl_id'];
				}else{
					return substr($result['pl_id'], 0, 3).sprintf("%07d", substr($result['pl_id'], 3)+1);
				}	
			}
			else{
				return $rtn['pl_id'];
			}
		}		
	}elseif(strpos($act, "credit_note") !== false){
        $sql = 'select cn_no from credit_note order by cn_no desc limit 1';
        $result = mysql_qone($sql);
        if($rtn['cn_no'] == ''){
            if($result['cn_no']){
                return substr($result['cn_no'], 0, 3).sprintf("%07d", substr($result['cn_no'], 3)+1);
            }else{
                return 'CN00000001';
            }
        }else{
            if(substr($rtn['cn_no'], 0 ,3) == substr($result['cn_no'], 0, 3)){
                if(substr($rtn['cn_no'], 3) > substr($result['cn_no'], 3)){
                    return $rtn['cn_no'];
                }else{
                    return substr($result['cn_no'], 0, 3).sprintf("%07d", substr($result['cn_no'], 3)+1);
                }
            }
            else{
                return $rtn['cn_no'];
            }
        }
    }elseif(strpos($act, "payment_new") !== false){
        $sql = 'select py_no from payment_new order by py_no desc limit 1';
        $result = mysql_qone($sql);
        if($rtn['py_no'] == ''){
            if($result['py_no']){
                return substr($result['py_no'], 0, 3).sprintf("%07d", substr($result['py_no'], 3)+1);
            }else{
                return 'PY00000001';
            }
        }else{
            if(substr($rtn['py_no'], 0 ,3) == substr($result['py_no'], 0, 3)){
                if(substr($rtn['py_no'], 3) > substr($result['py_no'], 3)){
                    return $rtn['py_no'];
                }else{
                    return substr($result['py_no'], 0, 3).sprintf("%07d", substr($result['py_no'], 3)+1);
                }
            }
            else{
                return $rtn['py_no'];
            }
        }
    }elseif(strpos($act, "qc_report") !== false){
        $sql = 'select qc_id from qc_report order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['qc_id']) || $rtn['qc_id'] == ''){
            if($result['qc_id']){
                return substr($result['qc_id'], 0, 3).sprintf("%07d", substr($result['qc_id'], 3)+1);
            }else{
                return 'QC00000001';
            }
        }else{
            if(substr($rtn['qc_id'], 0 ,3) == substr($result['qc_id'], 0, 3)){
                if(substr($rtn['qc_id'], 3) > substr($result['qc_id'], 3)){
                    return $rtn['qc_id'];
                }else{
                    return substr($result['qc_id'], 0, 3).sprintf("%07d", substr($result['qc_id'], 3)+1);
                }
            }
            else{
                return $rtn['qc_id'];
            }
        }
    }elseif(strpos($act, "add_item_transfer_form") !== false){
        $sql = 'select trans_form_id from warehouse_item_transfer_form order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['trans_form_id']) || $rtn['trans_form_id'] == ''){
            if($result['trans_form_id']){
                return substr($result['trans_form_id'], 0, 3).sprintf("%07d", substr($result['trans_form_id'], 3)+1);
            }else{
                return 'ITF0000001';
            }
        }else{
            if(substr($rtn['trans_form_id'], 0 ,3) == substr($result['trans_form_id'], 0, 3)){
                if(substr($rtn['trans_form_id'], 3) > substr($result['trans_form_id'], 3)){
                    return $rtn['trans_form_id'];
                }else{
                    return substr($result['trans_form_id'], 0, 3).sprintf("%07d", substr($result['trans_form_id'], 3)+1);
                }
            }
            else{
                return $rtn['trans_form_id'];
            }
        }
    }elseif(strpos($act, "add_factory_complaint_form") !== false){
        $sql = 'select fc_id from fty_complaint_form order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['fc_id']) || $rtn['fc_id'] == ''){
            if($result['fc_id']){
                return substr($result['fc_id'], 0, 3).sprintf("%07d", substr($result['fc_id'], 3)+1);
            }else{
                return 'FC00000001';
            }
        }else{
            if(substr($rtn['fc_id'], 0 ,3) == substr($result['fc_id'], 0, 3)){
                if(substr($rtn['fc_id'], 3) > substr($result['fc_id'], 3)){
                    return $rtn['fc_id'];
                }else{
                    return substr($result['fc_id'], 0, 3).sprintf("%07d", substr($result['fc_id'], 3)+1);
                }
            }
            else{
                return $rtn['fc_id'];
            }
        }
    }elseif(strpos($act, "add_factory_chargeback_form") !== false){
        $sql = 'select fcb_id from fty_chargeback_form order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['fcb_id']) || $rtn['fcb_id'] == ''){
            if($result['fcb_id']){
                return substr($result['fcb_id'], 0, 3).sprintf("%07d", substr($result['fcb_id'], 3)+1);
            }else{
                return 'FCB0000001';
            }
        }else{
            if(substr($rtn['fcb_id'], 0 ,3) == substr($result['fcb_id'], 0, 3)){
                if(substr($rtn['fcb_id'], 3) > substr($result['fcb_id'], 3)){
                    return $rtn['fcb_id'];
                }else{
                    return substr($result['fcb_id'], 0, 3).sprintf("%07d", substr($result['fcb_id'], 3)+1);
                }
            }
            else{
                return $rtn['fcb_id'];
            }
        }
    }elseif(strpos($act, "add_p_r_voucher") !== false){
        $sql = 'select prv_id from sys_p_r_voucher order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['prv_id']) || $rtn['prv_id'] == ''){
            if($result['prv_id']){
                return substr($result['prv_id'], 0, 3).sprintf("%07d", substr($result['prv_id'], 3)+1);
            }else{
                return 'PRV0000001';
            }
        }else{
            if(substr($rtn['prv_id'], 0 ,3) == substr($result['prv_id'], 0, 3)){
                if(substr($rtn['prv_id'], 3) > substr($result['prv_id'], 3)){
                    return $rtn['prv_id'];
                }else{
                    return substr($result['prv_id'], 0, 3).sprintf("%07d", substr($result['prv_id'], 3)+1);
                }
            }else{
                return $rtn['prv_id'];
            }
        }
    }elseif(strpos($act, "add_petty_cash_voucher") !== false){
        $sql = 'select pcv_id from sys_petty_cash_voucher order by id desc limit 1';
        $result = mysql_qone($sql);
        if(!isset($rtn['pcv_id']) || $rtn['pcv_id'] == ''){
            if($result['pcv_id']){
                return substr($result['pcv_id'], 0, 3).sprintf("%07d", substr($result['pcv_id'], 3)+1);
            }else{
                return 'PCV0000001';
            }
        }else{
            if(substr($rtn['pcv_id'], 0 ,3) == substr($result['pcv_id'], 0, 3)){
                if(substr($rtn['pcv_id'], 3) > substr($result['pcv_id'], 3)){
                    return $rtn['pcv_id'];
                }else{
                    return substr($result['pcv_id'], 0, 3).sprintf("%07d", substr($result['pcv_id'], 3)+1);
                }
            }else{
                return $rtn['pcv_id'];
            }
        }
    }
}


//2012.5.30 自动生成加单的ID，即在ID后面加A、B、C的
function autoGenerationAddID($id, $sign = ''){
	global $act, $add, $add_so;
	
	if(strpos($act, "modifysample_order") !== false){
        if($sign == 'rev'){
            $temp = substr($id, 0, 10).'-REV';
        }else{
            $temp = substr($id, 0, 10);
        }
		$rs = mysql_q('select id from sample_order where so_no like ?', '%' . $temp . '%');
		if($rs){
			$rtn = mysql_fetch();
            if($sign == 'rev'){
                return $temp . (count($rtn)+1);
            }else{
                //自动生成附属单A、B等的后缀
                return $temp . $add_so[count($rtn)];
            }
		}else{
            if($sign == 'rev'){
                return $temp.'1';
            }else{
                return false;
            }
		}
	}elseif(strpos($act, "modifyinvoice") !== false){
		$temp = substr($id, 0, 10);
		$rs = mysql_q('select vid from invoice where vid like ?', '%' . $temp . '%');
		if($rs){
			$rtn = mysql_fetch();
			return $temp . $add[count($rtn)];
		}else{
			return $temp . $add[0];	
		}		
	}
}


//2012.06.19 从Bulletin Board中正则找出ID并加上链接
function match_id($content){
	$matches = array();
	$replace = array();
	$rs = preg_match_all('/[A-Z]{2}\d{8}/', $content, $matches);
	if($rs){
		foreach($matches[0] as $v){
			$first_two_letter = substr($v, 0, 2);
			if($first_two_letter == 'PI'){
				$replace[] = '<a href="?act=com-modifyproforma&modid='.$v.'" />'.$v.'</a>';	
			}elseif($first_two_letter == 'PO'){
				$replace[] = '<a href="?act=com-modifypurchase&modid='.$v.'" />'.$v.'</a>';	
			}
		}
		for($i = 0; $i < count($matches[0]); $i++){
			$content = str_replace($matches[0][$i], $replace[$i], $content);
		}
	}
	//正则匹配到内容就替换ID为链接，如果匹配不到，还是返回原来的content
	return $content;	
}


/*模拟sqlserver中的datediff函数　　 caoli   于   2001-3-27   14:29:44   加贴在   PHP编程
****模拟sqlserver中的datediff函数*******   
$part   类型：string   
取值范围：year,month,day,hour,min,sec   
表示：要增加的日期的哪个部分   
$date1,$date2   类型：timestamp   
表示：要比较的两个日期   
返回   类型：数值   
**************结束*(*************/   
function datediff($part, $date1, $date2){   
	//$diff = $date2 - $date1;   
	$year1 = date( "Y",$date1);   
	$year2 = date( "Y",$date2);   
	$month2 = date( "m",$date2);   
	$month1 = date( "m",$date1);   
	$day2 = date( "d",$date2);   
	$day1 = date( "d",$date1);   
	$hour2 = date( "d",$date2);   
	$hour1 = date( "d",$date1);   
	$min2 = date( "i",$date2);   
	$min1 = date( "i",$date1);   
	$sec2 = date( "s",$date2);   
	$sec1 = date( "s",$date1);   
	
	$part = strtolower($part);   
	$ret = 0;   
	switch($part){   
		case "year":   
			$ret = $year2 - $year1;   
			break;
		case "month":   
			$ret = ($year2 - $year1) * 12 + $month2 - $month1;   
			break;
		case "day":   
			$ret = (mktime(0,0,0,$month2,$day2,$year2)-mktime(0,0,0,$month1,$day1,$year1))/(3600*24);   
			break;
		case "hour":   
			$ret = (mktime($hour2,0,0,$month2,$day2,$year2)-mktime($hour1,0,0,$month1,$day1,$year1))/3600;   
			break;   
		case "min":   
			$ret = (mktime($hour2,$min2,0,$month2,$day2,$year2)-mktime($hour1,$min1,0,$month1,$day1,$year1))/60;   
			break;   
		case "sec":   
			$ret = $date2 - $date1;   
			break;   
		default:   
			return $ret;   
			break;   
	}   
	return $ret;   
}


//各种汇率转换
function currencyTo($num, $c_from, $c_to){
	$rtn_from = mysql_qone('select rate from currency where type = ?', $c_from);
	$rtn_to = mysql_qone('select rate from currency where type = ?', $c_to);
	return ($num / $rtn_from['rate'] * $rtn_to['rate']);
}


//通过pid获得ajax choose_product_new.php 的效果，提供填入pi的product item数据
function choose_product_new($pid, $currency){
	global $pic_path_com, $pic_path_small;
	$setting_rtn = mysql_qone('select markup from setting');
	$rtn = mysql_qone('select description, cost_rmb, photos, scode, ccode, exclusive_to from product where pid = ?', $pid);
	if($rtn){
		if (is_file($pic_path_com . $rtn['photos']) == true) { 			
			$arr = getimagesize($pic_path_com . $rtn['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			
			//圖片大小超過100KB則進行壓縮
			//$rtn['photos']是原來的， $small_photo是縮小後的
			//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
			$small_photo = 's_' . $rtn['photos'];
			if(filesize($pic_path_com . $rtn['photos']) > 100){
				//縮小的圖片不存在才進行縮小操作
				if (!is_file($pic_path_small . $small_photo) == true) { 	
					makethumb($pic_path_com . $rtn['photos'], $pic_path_small . $small_photo, 's');
				}
			}
			
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			//連接到的是原始圖片，在頁面顯示的是縮小後的圖片
			$photo_string = '<a href="/sys/'.$pic_path_com . $rtn['photos'].'" target="_blank" title="'.$rtn['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
		}
		
		//如果有exclusive_to，則使用customer的markup值
		if( $rtn['exclusive_to'] != ''){
			$markup_rtn = mysql_qone('select markup_ratio from customer where cid = ?', $rtn['exclusive_to']);	
		}
		
		if( isset($currency) && $currency != ''){
			$currency_rtn = mysql_qone('select rate from currency where type = ?', $currency);
			$price = $rtn['cost_rmb'] * /*$setting_rtn['currency'] 20120423 弃用setting里的currency */ $currency_rtn['rate'] * ((isset($markup_rtn['markup_ratio']) && $markup_rtn['markup_ratio'] != '')?$markup_rtn['markup_ratio']:$setting_rtn['markup']);
			//price round為浮點數四捨五入，sprintf為保留兩位小數，不足的補0
			return formatMoney($price).'|'.$rtn['description'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'.$rtn['scode'];	
		}else{
			return false;	
		}
			
	}else{
		return false;	
	}
}


//在各个modify页面显示status
//20121125 现在放在h1标签里显示了，所以改了样式
function show_status($status){
	switch($status){
		case '(D)':
			$show_status = '<font color="#718BA4"><b>( D )-Draft</b></font>';
			$border_color = "#718BA4";
			//$show_status = '( D )-Draft';
			break;		
		case '(I)':
			$show_status = '<font color="#8000FF"><b>( I )-Incomplete</b></font>';
			$border_color = "#8000FF";
			//$show_status = '( I )-Incomplete';
			break;	
		case '(S)':
			$show_status = '<font color="#FF6600"><b>( S )-Shipped</b></font>';
			$border_color = "#FF6600";
			//$show_status = '( S )-Shipped';
			break;	
		case '(P)':
			$show_status = '<font color="#FF8080"><b>( P )-Paid</b></font>';
			$border_color = "#FF8080";
			//$show_status = '( P )-Paid';
			break;
		case '(C)':
			$show_status = '<font color="#40AA53"><b>( C )-Complete</b></font>';
			$border_color = "#40AA53";
			//$show_status = '( C )-Complete';
			break;
		default:
			$show_status = '<font color="#718BA4"><b>'.$status.'</b></font>';
			$border_color = "#718BA4";				
	}
	echo '<span style=" background-color:#fff; float:right; border:2px solid '.$border_color.'; ">'.$show_status.'</span>';
	//echo '<span style="float:right;">Status : <font size="4px">'.$show_status.'</font></span>';
}


//判断是否是sys的admin
function isSysAdmin(){
    //fb($_SESSION['logininfo']['aName']);
	if(isset($_SESSION['logininfo']['aName'])){
		$rs = mysql_q('select AdminName from tw_admin where AdminLuxGroup = ?', 'admin');
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				if($_SESSION['logininfo']['aName'] == $v['AdminName']){
					return true;	
				}
			}
		}else{
			return false;	
		}
	}else{
		return false;	
	}
	return false;
}


//检测提交的请假或加班的时间段有哪些有效时间点
function check_all_hr_time($type, $start_time, $end_time){
	//工作时间，数字指当前小时，如9指的是9点-10点这个小时
	$work_time = array(9, 10, 11, 12, 14, 15, 16, 17);	
	//记录所有合法的请假或加班的时间点，包括不合理的凌晨时间
	$hr_arr = array();

	//第一步：先都转为时间戳
	$temp_timestamp = $start_timestamp = strtotime($start_time);
	$end_timestamp = strtotime($end_time);
	
	//第二步：从开始每次加3600s，即1 hour， 直到结束时间
	while($temp_timestamp < $end_timestamp){
		$temp_date = date('Y-m-d H', $temp_timestamp);
		$for_holiday_function = date('m-d', $temp_timestamp);
		$temp_arr = explode(' ', $temp_date);
		//当前是星期几
		$temp_weekday = date('w', $temp_timestamp);
		if($type == 'OT'){
			if($temp_weekday == 0 || $temp_weekday == 6){
				//周末全天除1点外(因为如果不去掉13点，选择9-17点就有8小时了。。。)，都是合法ot时间,但是只记8小时
				if($temp_arr[1] != 13) $hr_arr[] = date('Y-m-d H:i:s', $temp_timestamp);
			}else{
                if(is_holiday($for_holiday_function)){
                    if($temp_arr[1] != 13) $hr_arr[] = date('Y-m-d H:i:s', $temp_timestamp);
                }else{
                    if(!in_array($temp_arr[1], $work_time)){
                        if($temp_arr[1] != 13) $hr_arr[] = date('Y-m-d H:i:s', $temp_timestamp);
                    }
                }
			}
		}elseif($type == 'LEAVE'){
			if($temp_weekday != 0 && $temp_weekday != 6){
				if(in_array($temp_arr[1], $work_time)){
					$hr_arr[] = date('Y-m-d H:i:s', $temp_timestamp);
				}			
			}
		}else{
			die('Type Error( in function : is_valid_hr_time )!');
		}
		$temp_timestamp += 3600;
	}
	$hr_arr = check_hr_hour($hr_arr);
	return array('num' => count($hr_arr), 'log_detail' => json_encode($hr_arr));
}


//加班或请假一天最多累计8小时（凌晨的时间会被算进去，后面的倒不会）
function check_hr_hour($arr){
	$first = explode(' ', @$arr[0]);
	$current_day = $first[0];
	$today_total_ot_hour = 0;
	$result_arr = array();
	foreach($arr as $v){
		$temp = explode(' ', $v);
		if($current_day != $temp[0]){
			$current_day = $temp[0];
			$today_total_ot_hour = 1;
			$result_arr[] = $v;	
		}else{
			if($today_total_ot_hour < 8){
				$result_arr[] = $v;
				$today_total_ot_hour++;	
			}
		}
	}
	return $result_arr;
}

//20131009 检测是否是法定假日
function is_holiday($date){
    $holiday = array('01-01', '01-02', '01-03', '05-01', '05-02', '05-03', '10-01', '10-02', '10-03', '10-04',
        '10-05', '10-06', '10-07');
    if(in_array($date, $holiday)){
        return true;
    }else{
        return false;
    }
}

//20130321 从PI转PO的时候，需要计算此PI之前已经转了多少，转过去以后就扣除之前转了的数量或款号，只显示剩下的
function pi_to_po_item_auto_calculate($pvid){
    //20170429 TEMP 不转
	$rs_pi = mysql_q('SELECT p.pid, p.cost_rmb as price, pi.quantity, p.description_chi, p.photos, p.ccode, p.scode FROM product p, proforma_item pi WHERE p.pid = pi.pid AND pi.pvid = ? and p.type <> ? and p.pid not like ? and p.pid not like ?', $pvid, 'Non-Product(TEMP)', 'TEMP%', 'temp%');
	if($rs_pi){
		$pi_item_rtn = mysql_fetch();
		
		//group by pid避免了出现多个相同pid在结果集中的情况
        //20130603 已delete的单不记入
		$rs_po = mysql_q('select poi.pid, sum(poi.quantity) as quantity from purchase po join purchase_item poi on po.pcid = poi.pcid where po.reference = ? and po.istatus <> ? group by poi.pid', $pvid, 'delete');
		//已存在从此PI转为PO的单了，就减去已转的，返回剩下的
		//如果不存在，则直接返回所有
		if($rs_po){
			$po_item_rtn = mysql_fetch();
			
			$result = array();
			$index = 0;
			foreach($pi_item_rtn as $v){
				$sign = 0;
				foreach($po_item_rtn as $w){
					//已转po的item数量大于等于pi中的数量，则不加入到result数组中返回
					if($v['pid'] == $w['pid']){
						if($v['quantity'] > $w['quantity']){
							$result[$index]['pid'] = $v['pid'];
							$result[$index]['price'] = $v['price'];
							$result[$index]['quantity'] = $v['quantity'] - $w['quantity'];//只返回剩下的数量
							$result[$index]['description_chi'] = $v['description_chi'];
							$result[$index]['photos'] = $v['photos'];
							$result[$index]['ccode'] = $v['ccode'];
							$result[$index]['scode'] = $v['scode'];
							$index++;
						}
						$sign = 1;//注意这个的位置，不能放上面if中，否则已经转完的item也会被下面重新插入
					}
				}
				if(!$sign){
					$result[$index]['pid'] = $v['pid'];
					$result[$index]['price'] = $v['price'];
					$result[$index]['quantity'] = $v['quantity'];
					$result[$index]['description_chi'] = $v['description_chi'];
					$result[$index]['photos'] = $v['photos'];
					$result[$index]['ccode'] = $v['ccode'];
					$result[$index]['scode'] = $v['scode'];
					$index++;					
				}
			}
			return $result;
		}else{
			return $pi_item_rtn;
		}
	}else{
		return false;	
	}
}


//20130625 从PI转Invoice的时候，需要计算此PI之前已经转了多少，转过去以后就扣除之前转了的数量或款号，只显示剩下的
function pi_to_invoice_item_auto_calculate($pvid){
    $rs_pi = mysql_q('SELECT p.pid, pi.price, pi.quantity, pi.description, p.photos, p.ccode, p.scode FROM product p, proforma_item pi WHERE  p.pid = pi.pid AND pi.pvid = ?', $_GET['pvid']);
    if($rs_pi){
        $pi_item_rtn = mysql_fetch();

        //group by pid避免了出现多个相同pid在结果集中的情况
        //20130603 已delete的单不记入
        $rs_invoice = mysql_q('select ii.pid, sum(ii.quantity) as quantity from invoice i join invoice_item ii on i.vid = ii.vid where i.vid like ? and i.istatus <> ? group by ii.pid', '%'.$pvid.'%', 'delete');
        //已存在从此PI转为invoice的单了，就减去已转的，返回剩下的
        //如果不存在，则直接返回所有
        if($rs_invoice){
            $invoice_item_rtn = mysql_fetch();

            $result = array();
            $index = 0;
            foreach($pi_item_rtn as $v){
                $sign = 0;
                foreach($invoice_item_rtn as $w){
                    //已转invoice的item数量大于等于pi中的数量，则不加入到result数组中返回
                    if($v['pid'] == $w['pid']){
                        if($v['quantity'] > $w['quantity']){
                            $result[$index]['pid'] = $v['pid'];
                            $result[$index]['price'] = $v['price'];
                            $result[$index]['quantity'] = $v['quantity'] - $w['quantity'];//只返回剩下的数量
                            $result[$index]['description'] = $v['description'];
                            $result[$index]['photos'] = $v['photos'];
                            $result[$index]['ccode'] = $v['ccode'];
                            $result[$index]['scode'] = $v['scode'];
                            $index++;
                        }
                        $sign = 1;//注意这个的位置，不能放上面if中，否则已经转完的item也会被下面重新插入
                    }
                }
                if(!$sign){
                    $result[$index]['pid'] = $v['pid'];
                    $result[$index]['price'] = $v['price'];
                    $result[$index]['quantity'] = $v['quantity'];
                    $result[$index]['description'] = $v['description'];
                    $result[$index]['photos'] = $v['photos'];
                    $result[$index]['ccode'] = $v['ccode'];
                    $result[$index]['scode'] = $v['scode'];
                    $index++;
                }
            }
            return $result;
        }else{
            return $pi_item_rtn;
        }
    }else{
        return false;
    }
}


/*//20130411 在各个modify页面显示status，用图片显示
function show_status_new($status){
	$show_status = '';
	//20130412 empty.gif 改成大图了，要定宽高都是32px
	switch($status){
		case 'delete':
			$show_status .= '<img title="Deleted" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/deleted.gif" />';
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			break;			
		case '(D)':
			$show_status .= '<img title="Draft" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/draft_small.gif" />';
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			break;		
		case '(I)':
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/Incomplete.gif" />';
			break;	
		case '(S)':
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/shipped.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			break;	
		case '(P)':
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/paid.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			break;
		case '(C)':
			$show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/complete.gif" />';
			$show_status .= '<img title="Paid" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Shipped" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			break;
		default:
			$show_status .= '<img style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
			$show_status .= '<img style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
	}
	echo $show_status;
}*/

//20130417 为了图标显示pi和po的进度，函数返回输入日期的前 $before 后 $after 天的日期数组
/*
function cal_date($date, $before, $after){
	$chart_date = array();
	$index = 0;
	for($i = $before; $i >= 0; $i--){
		$chart_date[$index++] = date("Y-m-d",strtotime($date) - 60*60*24*$i);
	}
	for($i = 1; $i <= $after; $i++){
		$chart_date[$index++] = date("Y-m-d",strtotime($date) + 60*60*24*$i);
	}
	return $chart_date;
}
*/

//20130527 检查是否数组中有重复的项，有返回true，没有返回false，参数pid是一维数组
/*function check_repeat_item($pid){
    $temp = array();
    $i = 0;
    foreach($pid as $v){
        if($i++ != 0){
            if(in_array($v, $temp)){
                fb($v);
                return true;
            }
        }
        $temp[] = $v;
    }
    return false;
}*/


//20130627 返回检测用户所在的小组的sql
//因为有引号，所以不能直接嵌入到这个系统的sql里去
/*function get_permission_sql(){
    $where_sql = " in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE '%".$_SESSION['logininfo']['aName']."%' OR AdminName = '".$_SESSION['logininfo']['aName']."')";
    return $where_sql;
}*/

//格式 ： KH20130706-01
function rsm_autoGenerationID($wh_name){
    $rtn = mysql_qone('select rsm_id from retail_sales_memo where in_date > ? and in_date < ? order by in_date desc', date('Y-m-d'), date("Y-m-d",strtotime("+1 day")));
    if($rtn){
        $rsm_id_array = explode('-', $rtn['rsm_id']);
        return $rsm_id_array[0].'-'.sprintf("%02d", intval($rsm_id_array[1])+1);
    }else{
        return $wh_name.date('Ymd').'-01';
    }
}