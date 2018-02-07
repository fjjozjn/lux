<?php
/**
   ----------------- 普通、通用函数页 ----------------
   
changelog

2009-08-13
2010-05-25		Add getLocation()
2010-05-27		将 getTitleByFromSelect 无值之情况改为返回 *已关闭*
				增加 unicodeDecode()，将 %uxxxx 转为php5可识别的utf8
2010-09-06		增加 getAppsUrl()
2010-12-07		修改 modelExist()，对inside_开头的model直接使用根目录下的model，后台不再放置inside_类型的model
2011-07-01		增加 setTrue()
*/


if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');


/*
 * 分页函数 // 2009.3.10修改
 * 参数:
 * $total, 总共多少页
 * $cur, 当前第几页
 * $pageLimit, 每页多少条纪录
 * $url, QueryString 后追加的url
*/
function showPageList($total, $cur, $pageLimit, $url = NULL, $simple =false){
	$page_round = 5; //每N个页面码, 出现"下N页"按钮
	$show_nextn = false; //是否出现"下N页"按钮
	
	if(!$url)
		$url = "";
	else
		$url= $url."&";
	if($total==0)return false;
	$page_num = ceil($total / $pageLimit);

	$s = '';
	if($cur > $page_num)$cur = $page_num; //指定页数超过实际页面时,设为最大页数
	$pageListStart = intval(($cur-1) / $page_round) * $page_round + 1; //求得「每N页」之开始页数
	
	$s .= '<div class="Pages">';
	$s .= '<div class="Paginator">';
	//取消页总数与条数的显示;
	//$s .= '<span class="Results">'.$cur.'/'.$page_num.'页,'.$total.'条</span>';
	//显示上一页
	if($page_num >1 && $cur > 1)
		$s .= '<a href="?'.$url.'page='.($cur-1).'" class="Prev">&lt;上页</a>';
	else
		$s .= '<span class="AtStart">&lt;上页</span>';
	//显示前10($page_round)页
	if($pageListStart != 1 && $show_nextn)
		$s .= '<a href="?'.$url.'page='.($pageListStart- $page_round).'" title="上'.$page_round.'页">&lt;&lt;</a>';
	//if($page_num==1)
		//$s .= '页码:';
	//当前页数大于10时,显示 首页(1)
	if($cur > $page_round)
		$s .= '<a href="?'.$url.'page=1">1</a><span class="break">...</span>';
	for($i=$pageListStart;$i<=$page_num;$i++){
		if($i<($pageListStart+$page_round)){
			if($i == $cur)
				$s .= '<span class="this-page">'.$i.'</span>';
			else
				$s .= '<a href="?'.$url.'page='.$i.'">'.$i.'</a>';
		}
	}
	if($cur < $page_num){
		//if($cur!=$page_num)
		//末页
		if($page_num > $page_round && ceil($page_num / $page_round) != ceil($pageListStart / $page_round) ){
			$s .= '<span class="break">...</span>';
			$s .= '<a href="?'.$url.'page='.$page_num.'">'.$page_num.'</a>';
		}
		//下10页
		if($cur <= $page_round && $page_num > $page_round && $show_nextn)
			$s .= '<a href="?'.$url.'page='.($pageListStart+$page_round).'" title="下'.$page_round.'页">&gt;&gt;</a>';
		//显示下一页
		$s .= '<a href="?'.$url.'page='.($cur+1).'" class="Next">下页&gt;</a>';
	}else{
		$s .= '<span class="AtEnd">下页&gt;</span>';
		//$s .= '<span class="AtEnd">&lt;|</span>';
	}
	if($page_num > $page_round && !$simple)
		$s .= '<label><span class="PageInputText">页码:</span><input name="input_page" type="text" id="input_page" size="4" maxlength="6" class="PageInputBox"></label> <input name="Submit_page" type="button" id="Submit_page" value="GO" class="PageInputBox" autocomplete="off" onClick="jump_page(\'?'.$url.'\');">';
	return $s;
}

/*
 * 为Forms服务的分页函数 // 2009.8.18创建
 * 参数:
 * $total, 总共多少页
 * $cur, 当前第几页
 * $pageLimit, 每页多少条纪录
 * $url, QueryString 后追加的url
*/
function showPageListForm($total, $cur, $pageLimit, $formName, $simple = true){
	$page_round = 5; //每N个页面码, 出现"下N页"按钮
	$show_nextn = false; //是否出现"下N页"按钮
	
	if($total==0)return '';
	$page_num = ceil($total / $pageLimit);

	$s = '';
	if($cur > $page_num)$cur = $page_num; //指定页数超过实际页面时,设为最大页数
	$pageListStart = intval(($cur-1) / $page_round) * $page_round + 1; //求得「每N页」之开始页数
	
	$s .= '<div class="Pages" id="p_'. $formName .'">';
	$s .= '<div class="Paginator">';
	//取消页总数与条数的显示;
	//$s .= '<span class="Results">'.$cur.'/'.$page_num.'页,'.$total.'条</span>';
	
	//显示上一页
	if($page_num >1 && $cur > 1)
		$s .= '<a href="javascript:void(0);" page="'.($cur-1).'" class="Prev">&lt;上页</a>';
	else
		$s .= '<span class="AtStart">&lt;上页</span>';
	
	//显示前10($page_round)页
	if($pageListStart != 1 && $show_nextn)
		$s .= '<a href="javascript:void(0);" page="'.($pageListStart- $page_round).'" title="上'.$page_round.'页">&lt;&lt;</a>';
	//if($page_num==1)
		//$s .= '页码:';
	//当前页数大于10时,显示 首页(1)
	if($cur > $page_round)
		$s .= '<a href="javascript:void(0);" page="1">1</a><span class="break">...</span>';
	for($i=$pageListStart;$i<=$page_num;$i++){
		if($i<($pageListStart+$page_round)){
			if($i == $cur)
				$s .= '<span class="this-page">'.$i.'</span>';
			else
				$s .= '<a href="javascript:void(0);" page="'.$i.'">'.$i.'</a>';
		}
	}
	if($cur < $page_num){
		//if($cur!=$page_num)
		//末页
		if($page_num > $page_round && ceil($page_num / $page_round) != ceil($pageListStart / $page_round) ){
			$s .= '<span class="break">...</span>';
			$s .= '<a href="javascript:void(0);" page="'.$page_num.'">'.$page_num.'</a>';
		}
		//下10页
		if($cur <= $page_round && $page_num > $page_round && $show_nextn)
			$s .= '<a href="javascript:void(0);" page="'.($pageListStart+$page_round).'" title="下'.$page_round.'页">&gt;&gt;</a>';
		//显示下一页
		$s .= '<a href="javascript:void(0);" page="'.($cur+1).'" class="Next">下页&gt;</a>';
	}else{
		$s .= '<span class="AtEnd">下页&gt;</span>';
		//$s .= '<span class="AtEnd">&lt;|</span>';
	}
	if($page_num > $page_round && !$simple){
		$s .= ' 页码:
		<input type="text" size="4" maxlength="6" class="noinit">
		<input type="button" class="choosepage" value="GO" autocomplete="off">';
	}
	return $s;
}


// 生成指定位数的随机字串
function randomString($len=6,$format='all') { 
	switch($format) {
		case 'full':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break;
		case 'upper':
			$chars='ABCEFGHJKMNPQRTWXY2346789';
			break;
		case 'all':
			$chars='ABCDEFGHJKMNPQRSTWXYabcdefghjkmnpqrstwxy123456789';
			break;
		case 'letter':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			break;
		case 'number':
			$chars='0123456789';
			break;
		default :
			$chars='ABCDEFGHJKMNPQRSTWXYabcdefghjkmnpqrstwxy123456789';
	}
	$password='';
	while(strlen($password) < $len)
		$password .= $chars[mt_rand(0, strlen($chars) -1)];
	return $password;
}

function checkEmail($email)
{
	return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/", $email);
}

function getContentByLine($file_path){
	ini_set('auto_detect_line_endings', 1);
	if(!file_exists($file_path)) die('系统错误 0x002');
	$c = array();
	$handle = @fopen($file_path, 'r');
	if($handle){
		while (!feof($handle)) {
			$buffer = trim(fgets($handle));
			if ($buffer){
				$c[] = $buffer;
			}
		}
		fclose($handle);
		return $c;
	}
	return false;
}

/* 判断输入的所有值是否为空
 * 可用于判断form有否填写的场合
 * 如 if(!havePost('input1', 'input2')) echo '请填写所有必填内容';
 *
 */
function havePost(){
	foreach(func_get_args() as $i){
		if(!set($_POST[$i])) return false;
	}
	return true;
}
function haveGet(){
	foreach(func_get_args() as $i){
		if(!set($_GET[$i])) return false;
	}
	return true;
}

// 读取request数据, 避免使用$_REQUEST
function getRequest($name){
	if(isset($_REQUEST[$name]) && !empty($_REQUEST[$name]))
		return $_REQUEST[$name];
	else
		return false;
}

// 检查一个变量是否有值。0 或 '0' 被视为有值。
// 如果指定了参数 $equal ，则只有当变量设定有值，并且等于 $equal 时返回真。
function set(&$v, $equal = ''){
	if(isset($v) && (($equal !== '' && $v === $equal) || ($equal === '' && (!empty($v) || $v === 0 || $v === '0')))){
		return true;
	}else{
		return false;
	}
}

//检查某变量是否存在并且值为真
function setTrue(&$v){
	return isset($v) && $v;
}

function isTrue(&$v){
	return isset($v) && $v;
}


/*
// These two function were Replaced by Forms class function
//
// 为radio或checkbox加上选中标志
function checked($this_var, $user_var){
	if(!$this_var || !$user_var){
		return '';
	}elseif(isId($this_var) && isId($user_var)){
		return (intval($this_var) == intval($user_var)) ? 'checked' : '';
	}else{
		return !strcmp($this_var, $user_var) ? 'checked' : '';
	}
}

// 为select加上选中标志
function selected($this_var, $user_var){
	if(!$this_var || !$user_var){
		return '';
	}else{
		return !strcmp($this_var, $user_var) ? 'selected' : '';
	}
}
*/

// 是否整数
function isId($id){
	return 0 === strcmp($id, intval($id));
}

// 是否有效的数字ID序列 必须由英文逗号(,)分隔，并且字串由数字组成
// 类似于: 522,452,11,55
// 多馀的(,) 将被视为非法，并令判断返回false
function isIdList($idlist){
	//$regex = "/^[0-9,\s]{1,999}$/";
	//return preg_match($regex, $idlist);
	if(!$idlist || trim($idlist) === '') return false;
	foreach(explode(',', $idlist) as $v){
		if('' === $v = trim($v)) return false;
		if(!isId($v)) return false;
	}
	return true;
}

// 是否有效的帐号.(只允许字母与数字 - )
//参数：	$str		帐号
//		$s			最短的长度
//		$e			最长的长度
//		$initial	首字是否必须字母
function vChar($str, $s, $e = 254, $initial = false, $zjn = false){
    //20130624 加多个参数 $zjn 去除url的字符限制，可自由跳转，风险什么的就不管了
    if($zjn){
        return true;
    }else{
        if($initial){
            $s--;
            if($e < 254)$e--;
            $regex = "/^[A-Za-z]{1}[A-Za-z0-9\-_]{".$s.(($e && $e != $s) ? ','.$e : '')."}$/";
        }else{
            //zjn 修改，为了可以自由加参数跳转，可能有隐患，但是我还不知道有什么隐患，就先这样吧
            $regex = "/^[A-Za-z0-9\-_&=]{".$s.(($e && $e != $s) ? ','.$e : '')."}$/";
        }
	    return preg_match($regex, $str);
    }
}

// 与vChar几乎相同作用. *old function*
function isAcc($str, $s = 5, $e = 16){
	return vChar($str, $s, $e);
}

// vChar的序号版，用来判断位数在15-20位之间的卡号序列 *old function*
function isCard($str, $s = 15, $e = 20){
	return vChar($str, $s, $e);
}

// vChar的序号版，用来判断位数在15-20位之间的卡号序列 *old function*
function isDate($str){
	if(preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $str) && false !== $date = strtotime($str)){
		return $date;
	}else{
		return false;
	}
}

// 禁止 register_globals
function unregister_GLOBALS() {
	if ( !ini_get('register_globals') )
		return;

	if ( isset($_REQUEST['GLOBALS']) )
		die('GLOBALS overwrite attempt detected');

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v ){
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = NULL;
			unset($GLOBALS[$k]);
		}
	}
}

//display array for debug
function print_r_pre($input){
	echo "<div align='left'><pre>";
	print_r($input);
	echo "</pre></div>";
}

function printJs($js){
	return '<script language="javascript">'."\n".$js."\n</script>\n";
}

/*
 * 返回各种类型的时间与日期
 */
function dateMore($type = 'dt'){
	switch($type){
		case 'date':
			//日期
			$rtn = date('Y-m-d');
			break;
		case 'dt':
			//日期与时间
			$rtn = date('Y-m-d H:i:s');
			break;
		case 'mt':
            //返回14位时间戳
			$rtn = microtime(true);
            $rtn = str_replace('.', '', $rtn);
			break;
		case 'fmt':
			//返回带微秒小数(6位)的timestamp的字串
			$tmp = explode(' ', microtime());
			$rtn = $tmp[1] . substr($tmp[0], 1, 7);
			break;
		default:
			$rtn = date('Y-m-d H:i:s');
			break;
	}
	return $rtn;
}

/*
 * 从多维数组中查找是否有指定路径顺序的key/value
 * 比如现在有:
 * $A1 = array('test', 'test2' => array('test3', 'test4' => array('test5'))) 中
 * 现在要查找：$A2 = array('test2', 'test4', 'test5');
 * $A2 是一个序列，表示在 $A1 是否有 key 'test2' > key 'test4' > value 'test5' 的存在
 */
function inMyArray($v, $array){
	$n = count($v);
	if($n == 1){
		return in_array($v[0], $array);
	}else{
		for($i = 0; $i < $n; $i++){
			if($i < $n - 1){
				if(!array_key_exists($v[$i], $array)){
					return false;
				}else{
					$array = $array[$v[$i]];
				}
			}else{
				return in_array($v[$i], $array);
			}
		}
	}
}

/*
 * 判断指定的model文件是否存在
 */
function modelExist($act, $admin = false){
	if(!defined('BEEN_INCLUDE')) return false;
	if(!vChar($act, 2, 254, true)) return false;
	
	// convert URL to PATH
	$act = str_replace('-', '/', $act);
	$file = ROOT_DIR. ($admin && strpos($act, 'inside_') === false ? 'sys/' : '') .'model/'. $act .'.php';
	if(is_file($file)){
		return $file;
	}else{
		return false;
	}
}

/*
 * 判断指定的model文件是否存在 add by zjn 20120409 因有新的fty目录，所以重写modelExist2
 */
function modelExist2($act, $admin = false){
	if(!defined('BEEN_INCLUDE')) return false;
	if(!vChar($act, 2, 254, true)) return false;
	// convert URL to PATH
	$act = str_replace('-', '/', $act);
	$file = ROOT_DIR. ($admin && strpos($act, 'inside_') === false ? 'fty/' : '') .'model/'. $act .'.php';
	if(is_file($file)){
		return $file;
	}else{
		return false;
	}
}

/*
 * 判断指定的model文件是否存在 add by zjn 20131017 因有新的luxcraft目录，所以重写modelExist3
 */
function modelExist3($act, $admin = false){
    if(!defined('BEEN_INCLUDE')) return false;
    if(!vChar($act, 2, 254, true)) return false;
    // convert URL to PATH
    $act = str_replace('-', '/', $act);
    $file = ROOT_DIR. ($admin && strpos($act, 'inside_') === false ? 'luxcraft/' : '') .'model/'. $act .'.php';
    if(is_file($file)){
        return $file;
    }else{
        return false;
    }
}

/*
 * 生成交易号
 * 最长32位, 最短...不建议太短...有可能出现重複的情况
 */
function genTradeNo($for, $length = 20){
	$maxLength = 30;
	$str = $for . time();
	$strLen = strlen($str);
	if($length < strlen($for) + 3){
		return 'Wrong length';
	}elseif($strLen + 6 > $length){
		// 返回以$for开头，以md5值为中间，以100-999随机数结尾的字串.
		//return substr($for . md5($for . TIME . mt_rand(100, 999)), 0, $length - strlen($for) - 3) . mt_rand(100, 999);
		return $for . substr(md5($for . time() . mt_rand(100, 999)), 0, $length - strlen($for) - 3) . mt_rand(100, 999);
	}elseif($length <= $maxLength){
		// 返回以$for开头，以timestamp为中间，以带微秒的timestamp与100-999随机数的md5 hash前N位 + 100-999随机数结尾的字串
		$tmp = explode(' ', microtime());
		$fmt = $tmp[1] . $tmp[0] . mt_rand(100, 999);
		return  $str . substr(md5($fmt), 3 - ($length - $strLen)) . mt_rand(100, 999);
	}else{
		return 'Wrong length';
	}
}

/*
 * 为 mysql 语句生成一定数量的?
 */
function moreQm($i){
	if($i > 1)
		return '? '.str_repeat(', ?', $i - 1);
	else
		return '?';
}

/*
 * 为 $gameList 查找对应的游戏名
 */
function getTitleByFromSelect($input_arr, $value){
	foreach($input_arr as $row){
		if ($row[1] == $value){
			return $row[0];
		}
	}
	return '*已关闭*';
}
/*
 * 根据 gameId 查找对应的游戏简称(short name)
 */
function gameinfos($input_arr, $value){
	foreach($input_arr as $row){
		if ($row[1] == $value){
			return $row;
		}
	}
	return false;
}

/*
 * 为 $gameList 查找所属资料
 */
function getDetailByFromSelect($input_arr, $value){
	foreach($input_arr as $row){
		if ($row['value'] == $value){
			return $row;
		}
	}
	return '';
}

/* 取得php版本的数字 (3位，530) */
/* 不再需要 
function phpver(){
	$v = explode('.', PHP_VERSION);
	return intval($v[0]. $v[1] .'0');
}
*/

function getLocation(){
	return ($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
}

function unicodeDecode($str){
    return preg_replace("/%u([0-9A-F]{4})/ie", "iconv('utf-16', 'utf-8', hex2str(\"$1\"))", $str);
}

function hex2str($hex) {
    $r = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2)
    $r .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    return $r;
}

/* 使用 curl 获取网页资料 */
function curl_file_get_contents($durl){
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $durl);
   curl_setopt($ch, CURLOPT_TIMEOUT, 25);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $r = curl_exec($ch);
   curl_close($ch);
   return $r;
}

/* 获取Apps的接入页 */
function getAppsUrl(){
	if(!set($_GET['fromapps'])) return '';
	if(set($GLOBALS[$_GET['fromapps'] . 'Domain'])){
		if(userSession('refer') && strpos(userSession('refer'), $GLOBALS[$_GET['fromapps'] . 'Domain']) === 0){
			return userSession('refer');
		}else{
			return $GLOBALS[$_GET['fromapps'] . 'Domain'];
		}
	}else{
		return '';
	}
}

//判断用户浏览器
function getBroswer(){
	if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE"))
		return 'ie';
	elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Firefox"))
		return 'ff';
	elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Chrome"))
		return 'gg';
	elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Safari"))
		return 'ap';
	elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Opera"))
		return 'op';
}

//多维数组转为字符串
function arrayToString($arr){
	if (is_array($arr)){
		return implode(',', array_map('arrayToString', $arr));
	}
	return $arr;
}

//红色字体
function redFont($str){
	return '<font color="#FF0000">'.$str.'</font>';	
}

//20130813 从sys/admin_function.php 转移过来，因sys和fty都要用
function cal_date_new($a_time,$b_time)
{
    $a = explode('-',$a_time);
    $b = explode('-',$b_time);
    $a_unix = mktime(0,0,0,$a[1],$a[2],$a[0]);
    $b_unix = mktime(0,0,0,$b[1],$b[2],$b[0]);
    $time_diff = $b_unix - $a_unix;
    $day_num = floor($time_diff / (24*3600));
    for($i = 0; $i <= $day_num; $i++)
    {
        $arr[] = date("Y-m-d",strtotime("+$i day", $a_unix));
    }
    return $arr;
}

//20130817 返回当前日期是星期几
function get_week($week){
    switch($week){
        case 0:
            return 'SUN';
        case 1:
            return 'MON';
        case 2:
            return 'TUE';
        case 3:
            return 'WED';
        case 4:
            return 'THU';
        case 5:
            return 'FRI';
        case 6:
            return 'SAT';
    }
}

//20130825 payment advice update pi status
function update_pi_status($pis){
    //返回状态转变的提示
    $pi_change_status_hints = '';

    foreach($pis as $pi){
        //$total sum pi item price*quantity
        $total = 0;
        $rtn = mysql_qone('select sum(price*quantity) as total_pi from proforma_item where pvid = ?', $pi);
        if(isset($rtn['total_pi']) && $rtn['total_pi'] != ''){
            $total += $rtn['total_pi'];
        }

        //$cn_total sum cn amount
        $cn_total = 0;
        //20141223 CREDIT NOTE的数值不影响pi的status
        /*
        $rtn = mysql_qone('select sum(amount) as total_amount from credit_note_item where cn_no = (select cn_no from credit_note where pvid = ?)', $pi);
        if(isset($rtn['total_amount']) && $rtn['total_amount'] != ''){
            $cn_total += $rtn['total_amount'];
        }
        */

        //$temp sum payment and payment advice
        $payment_total = 0;
        //20130825 payment_new 和 payment 重复了，不能减两次
/*        $rtn = mysql_qone('select sum(amount) as total_amount from payment where pi_no = ?', $pi);
        if(isset($rtn['total_amount']) && $rtn['total_amount'] != ''){
            $payment_total += $rtn['total_amount'];
        }*/
        //20130824 payment_item_new 里的的 received 也一起减去
        $rtn = mysql_qone('select sum(received) as total_received from payment_item_new where pi_or_cn = ? and pi_or_cn_no = ?', 'PI', $pi);
        if(isset($rtn['total_received']) && $rtn['total_received'] != ''){
            $payment_total += $rtn['total_received'];
        }

        $balance = $total - $cn_total - $payment_total;
        //fb($balance);

        $rtn = mysql_qone('select istatus from proforma where pvid = ?', $pi);
        if(my_formatMoney($balance) == 0){
            if($rtn['istatus'] == '(I)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(P)', $pi);
                mysql_q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$pi.'%');
                $pi_change_status_hints .= $pi . ' status change from (I) to (P); ';
            }elseif($rtn['istatus'] == '(S)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(C)', $pi);
                mysql_q('update invoice set istatus = ? where vid like ?', '(C)', '%'.$pi.'%');
                $pi_change_status_hints .= $pi . ' status change from (S) to (C); ';
            }
        }else{
            if($rtn['istatus'] == '(P)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(I)', $pi);
                mysql_q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$pi.'%');
                $pi_change_status_hints .= $pi . ' status change from (P) to (I); ';
            }elseif($rtn['istatus'] == '(C)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(S)', $pi);
                mysql_q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$pi.'%');
                $pi_change_status_hints .= $pi . ' status change from (C) to (S); ';
            }
        }
    }
    return $pi_change_status_hints;
}

//20131212 传入product名，返回<img>产品图片标签
function product_img($photo){
    global $pic_path_com, $pic_path_small;
    $photo_string = '';
    if (is_file($pic_path_com . $photo) == true) {

        //图片压缩
        //$photo是原來的， $small_photo是縮小後的
        //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
        $small_photo = 's_' . $photo;
        //縮小的圖片不存在才進行縮小操作
        if (!is_file($pic_path_small . $small_photo) == true) {
            makethumb($pic_path_com . $photo, $pic_path_small . $small_photo, 's');
        }
        /*
        $arr = getimagesize($pic_path_com . $rsm_item_rtn[$i]['photo']);
        $pic_width = $arr[0];
        $pic_height = $arr[1];
        $image_size = getimgsize(80, 60, $pic_width, $pic_height);
        $photo_string = '<a href="/sys/'.$pic_path_com . $rsm_item_rtn[$i]['photo'].'" target="_blank" title="'.$rsm_item_rtn[$i]['photo'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
        */
        $photo_string = '<a href="/sys/'.$pic_path_com . $photo.'" target="_blank" title="'.$photo.'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
    }else{
        $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
    }
    return $photo_string;
}

//20140106 从sys 和 fty/in38/admin_function.php 移到这里 （不能放这里，因为pdf是用的global_pdf.php）
//格式化显示钱：保留两位小数，不足的补0，整数部分每三位逗号隔开
/*function formatMoney($money){
    return fmoney(sprintf("%01.2f", round(floatval($money), 2)));
}*/

//20140106 从sys 和 fty/in38/admin_function.php 移到这里 （不能放这里，因为pdf是用的global_pdf.php）
//20130812 数字中有逗号不方便提交，所以只保留两位小数，不需要逗号分隔
/*function my_formatMoney($money){
    return sprintf("%01.2f", round(floatval($money), 2));
}*/

//20140106 （payment new 只有两个状态）modify页面显示status，用图片显示
function show_status_payment_new($status){
    $show_status = '';
    //20130412 empty.gif 改成大图了，要定宽高都是32px
    switch($status){
        case '(I)':
            $show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
            $show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/Incomplete.gif" />';
            break;
        case '(C)':
            $show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/complete.gif" />';
            $show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
            break;
        default:
            $show_status .= '<img title="Complete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
            $show_status .= '<img title="Incomplete" style="float:right; margin:4px 4px 0 0" width="32px" height="32px" src="../../images/empty.gif" />';
    }
    echo $show_status;
}

//在各个modify页面显示status，用图片显示
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
}

//20140629 通过vid获取invoice item的总数
function get_invoice_items_total_num($vid){
    $rtn = mysql_qone('select sum(quantity) as total_num from invoice_item where vid = ?', $vid);
    return $rtn['total_num']?$rtn['total_num']:0;
}
//20141222 统一istatus的颜色
function get_status_color($istatus){
    if($istatus == 'delete'){
        return 'black';
    }elseif($istatus == '(D)'){
        return 'gray';
    }elseif($istatus == '(I)'){
        return 'purple';
    }elseif($istatus == '(S)'){
        return '#FF7D00';
    }elseif($istatus == '(P)'){
        return '#FFFF00';
    }elseif($istatus == '(C)'){
        return 'green';
    }
}
//20150401 数字中有逗号不方便提交，所以只保留两位小数，不需要逗号分隔
function formatNumToMoney($money){
    return sprintf("%01.2f", round(floatval($money), 2));
}








/*************************************************
 * 返回一个数组变量，供selctbox用，不需要查询数据库的！这样的好处是不用每个页面都初始化这些变量，需要用的页面调函数就行了！
 */
//20130625 warehouse
function get_payment_method(){
    return array(
        array('Cash','Cash'),
        array('China UnionPay','China UnionPay'),
        array('Credit Card','Credit Card'),
        array('EPS','EPS')
    );
}
//20130730
function get_bank_acc(){
    return array(
        array('HK(HKD)', 'HK(HKD)'),
        array('HK(USD)', 'HK(USD)'),
        array('SZ(CASH)', 'SZ(CASH)'),
        array('SZ(COMPANY)', 'SZ(COMPANY)'),
        array('Cash/Cheque', 'Cash/Cheque')
    );
}
//20130730
function get_pi_or_cn_type(){
    return array(
        array('PI', 'PI'),
        array('CN', 'CN'),
        array('CUSTOMER BANK CHARGE', 'CUSTOMER BANK CHARGE'),
    );
}
//20130814 工厂的颜色
function fty_color($sid){
    $temp = substr($sid, 3, 1);
    $color = '#'.$temp.$temp.$temp.$temp.$temp.$temp;
    return array(
        $sid=>$color,
    );
}
//20130930 hr
function get_hr_type($type){
    //员工填写OT 或 LEAVE 的时候不能选ADD ANNUAL LEAVE HOURS，因为是系统自动生成的，但是查询的时候可选，方便大家查询
    if($type == 1){
        return array(array('OT', 'OT'), array('LEAVE', 'LEAVE'));
    }elseif($type == 2){
        return array(array('OT', 'OT'), array('LEAVE', 'LEAVE'), array('SYSTEM ADD ANNUAL LEAVE HOURS (AUTO)',
            'SYSTEM ADD ANNUAL LEAVE HOURS (AUTO)'));
    }
}
//20140106
function get_payment_advice_status(){
    return array(
        array('Incomplete', '(I)'),
        array('Complete', '(C)')
    );
}
//20140108
function get_product_type(){
    return array(
        array('Necklace', 'Necklace'),
        array('Earrings', 'Earrings'),
        array('Ring', 'Ring'),
        array('Bracelet', 'Bracelet'),
        array('Bangle', 'Bangle'),
        array('Brooch', 'Brooch'),
        array('Set', 'Set'),
        array('Other', 'Other'),
    );
}
//20140226
function get_user_type(){
    return array(
        array('sys', 'sys'),
        array('fty', 'fty'),
        array('luxcraft', 'luxcraft'),
    );
}
function get_courier_or_forwarder(){
    return array(
        array('DHL', 'DHL'),
        array('Fedex', 'Fedex'),
        array('UPS', 'UPS'),
        array('Speedmark', 'Speedmark'),
        array('Kesco', 'Kesco'),
        array('Toll Global', 'Toll Global'),
        array('LF Logistic', 'LF Logistic'),
        array('Air City', 'Air City'),
        array('Other', 'Other')
    );
}
//20150106
function get_po_status(){
    return array(
        array('Draft', '(D)'),
        array('Incomplete', '(I)'),
        array('Shipped', '(S)'),
        array('Paid', '(P)'),
        array('Complete', '(C)'),
    );
}
//20150122 称呼
function get_title(){
    return array(
        array('Mr', 'Mr'),
        array('Mrs', 'Mrs'),
        array('Ms', 'Ms'),
    );
}
//20151202 customer nature
function get_nature(){
    return array(
        array('Brand owner', 'Brand owner'),
        array('Retailers', 'Retailers'),
        array('Wholesaler', 'Wholesaler'),
        array('Trader', 'Trader'),
        array('Manufacturer', 'Manufacturer'),
        array('Other', 'Other'),
    );
}
//20151202 customer service_required
function get_service_required(){
    return array(
        array('OEM', 'OEM'),
        array('Open lines', 'Open lines'),
        array('Design only', 'Design only'),
    );
}
//20151202 customer affordable_pricing
function get_affordable_pricing(){
    return array(
        array('High', 'High'),
        array('Medium', 'Medium'),
        array('Low', 'Low'),
    );
}
//20151202 customer quality_requirement
function get_quality_requirement(){
    return array(
        array('Extremely High', 'Extremely High'),
        array('High', 'High'),
        array('Medium High', 'Medium High'),
        array('Medium', 'Medium'),
        array('Low', 'Low'),
    );
}
//20151202 customer business_potential
function get_business_potential(){
    return array(
        array('High', 'High'),
        array('Medium', 'Medium'),
        array('Low', 'Low'),
    );
}
//20151202 customer restricted_substance_requirement
function get_restricted_substance_requirement(){
    return array(
        array('CA65', 'CA65'),
        array('REACH', 'REACH'),
        array('Only Lead-free', 'Only Lead-free'),
        array('Only Nickel-free', 'Only Nickel-free'),
        array('No', 'No'),
    );
}
//20151202 customer lab_test_required
function get_lab_test_required(){
    return array(
        array('Yes', 'Yes'),
        array('No', 'No'),
    );
}
//20151202 customer factory_audit
function get_factory_audit(){
    return array(
        array('Yes, by 3rd Party', 'Yes, by 3rd Party'),
        array('Yes, own Audit team', 'Yes, own Audit team'),
        array('No', 'No'),
    );
}
//20151202 customer business_contract_required
function get_business_contract_required(){
    return array(
        array('Yes', 'Yes'),
        array('No', 'No'),
    );
}
//20160414
function get_payment_request_approve_status(){
    return array(
        array('Approved', '1'),
        array('Pending', '0'),
        array('Paid', '2'),
    );
}
//20160523
function get_all_country(){
    $countries = array(
        "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica",
        "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain",
        "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina",
        "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso",
        "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile",
        "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the",
        "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark",
        "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea",
        "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan",
        "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana",
        "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
        "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland",
        "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan",
        "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan",
        "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein",
        "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives",
        "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of",
        "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal",
        "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island",
        "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines",
        "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis",
        "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal",
        "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa",
        "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname",
        "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China",
        "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey",
        "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States",
        "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)",
        "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"
    );
    $country_array = array();
    foreach($countries as $item){
        $country_array[] = array($item, $item);
    }
    return $country_array;
}
//20160605
//function get_it_request_location(){
//    return array(
//        array('香港公司', '香港公司'),
//        array('香港展廳', '香港展廳'),
//        array('深圳公司', '深圳公司'),
//        array('工廠', '工廠'),
//        array('零售店', '零售店'),
//        array('其他', '其他'),
//    );
//}
//20160610
function get_sys_department(){
    return array(
        array('Sales', 'Sales'),
        array('Design', 'Design'),
        array('Accounting', 'Accounting'),
        array('IT', 'IT'),
        array('Admin HR', 'Admin HR'),
        array('Production', 'Production'),
        array('Sample Development', 'Sample Development'),
        array('QC', 'QC'),
        array('Purchasing', 'Purchasing'),
        array('Other', 'Other'),
    );
}
//20171208
function get_fty_wlgy_jg_type(){
    return array(
        array('物料', '1'),
        array('加工', '2'),
    );
}


/*************************************************
 * 返回一个数组变量，供selctbox用，需要查询数据库的！
 */
//20130625 warehouse
//$result_type 区分返回结果是带id还是不带 1：带id但显示中文名 2：带id但显示中文名 其他值或空都不带（中文名）
//$type where中的type Warehouse：公司 Shop：店 空：全部
//20141118 pos加permission true:区分是否是admin，如不是只显示自己的 false：都显示全部
//20141113 都改为显示中文名
function get_warehouse_info($result_type = '', $type = '', $permission = false){
    $where = '';
    if(!$permission){
        if($type != ''){
            $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse where type = ? order by id", $type);
        }else{
            $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse order by id");
        }
    }else{
        if(isLuxcraftAdmin()){
            if($type != ''){
                $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse where type = ? order by id", $type);
            }else{
                $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse order by id");
            }
        }else{
            if($type != ''){
                $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse where wh_name = (select wh_name from tw_admin where AdminName = ?) and type = ? order by id", $_SESSION['luxcraftlogininfo']['aName'], $type);
            }else{
                $rs_w = mysql_q("select id, wh_name, wh_name_chi from warehouse where wh_name = (select wh_name from tw_admin where AdminName = ?) order by id", $_SESSION['luxcraftlogininfo']['aName']);
            }
        }
    }

    $wh_id = array();
    if($rs_w){
        $rows = mysql_fetch();
        foreach($rows as $v){
            if($result_type == 1){
                $wh_id[] = array($v['wh_name_chi'], $v['id'].'|'.$v['wh_name']);
            }elseif($result_type == 2){
                $wh_id[] = array($v['wh_name_chi'], $v['id'].'|'.$v['wh_name']);
            }else{
                $wh_id[] = array($v['wh_name_chi'], $v['wh_name']);
            }
        }
    }
    return $wh_id;
}
//20130715 get sample order no
function get_sample_order_no(){
    $sample_order_no = array();
    //20130724 cindy要求改在add product的sample order no选择框里只显示自己开的的
    if (isSysAdmin()){
        $rs = mysql_q('select so_no from sample_order order by so_no desc');
    }else{
        $rs = mysql_q('select so_no from sample_order where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by so_no desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $sample_order_no[] = array($v['so_no'], $v['so_no']);
        }
    }
    return $sample_order_no;
}
//20130715 get sample order no
function get_sample_order_no_fty(){
    $sample_order_no = array();
    $rs = mysql_q('select so_no from sample_order order by so_no desc');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $sample_order_no[] = array($v['so_no'], $v['so_no']);
        }
    }
    return $sample_order_no;
}
//20130725
function get_pid_warehouse_info($pid){
    $info = array();
    $rs = mysql_q('select wh_name, qty from warehouse_item_unique where pid = ? and qty > 0', $pid);
    if($rs){
        $rtn = mysql_fetch();
        foreach($rtn as $v){
            $info[] = $v['wh_name'].' : '.$v['qty'];
        }
    }
    return $info;
}
//20130730 get currency 不在放在admin_var里了
function get_currency_type(){
    $currency = array();
    $rs = mysql_q('select * from currency');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $currency[$v['type']] = array($v['type'], $v['type']);
        }
    }
    return $currency;
}
//20130731 get customer ，$customer变量用到的地方太多了，不知道都改完了没有
function get_customer(){
    $customer = array();
    if (!isSysAdmin()){
        // mod 4.4 只显示当前用户group创建的customer，因customer在公司内部属于机密内容，涉及员工利益
        // mod 20120723 customer应该是公用的，不能用新的规则
        // 20120723 下午 旧的当group中有多个名字的时候也不适用
        //$rs = mysql_q('select cid, name from customer where created_by in (select AdminName from tw_admin where AdminLuxGroup = (select AdminLuxGroup from tw_admin where AdminName = ?))', $_SESSION['logininfo']['aName']);
        //20150601 修改为普通用户只能查看自己group的用户的信息，也就是主可以查附属的，附属的不能查主的
        //20160906 一个人同是属于两个group时，用like判断
        $rs = mysql_q('select cid, name from customer where created_by in (SELECT AdminName FROM tw_admin WHERE AdminName = ? OR AdminLuxGroup like ?)', $_SESSION['logininfo']['aName'], '%'.$_SESSION['logininfo']['aName'].'%');

        //$rs = $mysql->q('select cid, name from customer where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        /*
        $rtn = $mysql->q('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
        $group_array = explode("\r\n", $rtn['AdminLuxGroup']);
        $all = '';
        foreach($group_array as $v){
            if($v != ''){
                $all .= $v.',';
            }
        }
        $rs = $mysql->q('select cid, name from customer where created_by in (?) or created_by = ?', $all, $_SESSION['logininfo']['aName']);

        if($rs){
            $rows = $mysql->fetch();
            foreach($rows as $v){
                $customer[$v['cid']] = array($v['name'], $v['cid']);
                $customer_so[] = array($v['name'], $v['name']); 	//SO的是要显示给工厂看的，所以是显示工厂全名
            }
            $customer['TEMP'] = array('TEMP', 'TEMP');//这是为了给每个用户都有一个TEMP的customer可用，由于admin已有了，所以这个操作也只是覆盖之前的，不影响
        }
        */
    }else{
        $rs = mysql_q('select cid, name from customer');
    }
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['name'], $v['cid']);
            $customer_so[] = array($v['name'], $v['name']); 	//SO的是要显示给工厂看的，所以是显示工厂全名
        }
        $customer['TEMP'] = array('TEMP', 'TEMP');//这是为了给每个用户都有一个TEMP的customer可用，由于admin已有了，所以这个操作也只是覆盖之前的，不影响
    }
    return $customer;
}
//20160529
function get_customer_fty(){
    $customer = array();
    $rs = mysql_q('select cid from customer');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['cid'], $v['cid']);
        }
        $customer['TEMP'] = array('TEMP', 'TEMP');//这是为了给每个用户都有一个TEMP的customer可用，由于admin已有了，所以这个操作也只是覆盖之前的，不影响
    }
    return $customer;
}
//20150704
function get_wlgy_customer(){
    $customer = array();
    $rs = mysql_q('select cid, name from fty_wlgy_customer');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['name'], $v['cid']);
        }
    }
    return $customer;
}
//20150705
function get_jg_customer(){
    $customer = array();
    $rs = mysql_q('select cid, name from fty_jg_customer');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['name'], $v['cid']);
        }
    }
    return $customer;
}
//20130805 get pvid only for payment new
function get_payment_no_py(){
    global $act;
    $pvid = '';
    if (isSysAdmin()){
        //20130812 先把 I S 的限制去掉，方便绑定旧的payment数据
        //20130823 payment_new 用只有 I 和 S 的，其他的不变
        if(strpos($act, 'payment_new') !== false){
            $rs = mysql_q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) order by mark_date desc', 'delete', '(I)', '(S)');
        }else{
            $rs = mysql_q('select pvid from proforma where istatus <> ? order by mark_date desc', 'delete');
        }
    }else{
        //20130812 先把 I S 的限制去掉，方便绑定旧的payment数据
        //20130823 payment_new 用只有 I 和 S 的，其他的不变
        if(strpos($act, 'payment_new') !== false){
            //$rs = mysql_q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
            //20140325 队员开的单队长看不到，所以改了
            $rs = mysql_q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by mark_date desc', 'delete', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }else{
            //$rs = mysql_q('select pvid from proforma where istatus <> ? and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
            //20140325 队员开的单队长看不到，所以改了
            $rs = mysql_q('select pvid from proforma where istatus <> ? and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $pvid[$v['pvid']] = array($v['pvid'], $v['pvid']);
        }
    }
    return $pvid;
}
//20130805 get pvid (暂时还没有把用 admin_var.php $pvid 的统一过来)
function get_proforma_no(){
    $pvid = '';
    if (isSysAdmin()){
        $rs = mysql_q('select pvid from proforma where istatus <> ? order by pvid desc', 'delete');
    }else{
        //$rs = $mysql->q('select pvid from proforma where pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = ?))) order by mark_date desc', $_SESSION['logininfo']['aName']);
        //$rs = mysql_q('select pvid from proforma where istatus <> ? and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        //20140325 队员开的单队长看不到，所以改了
        $rs = mysql_q('select pvid from proforma where istatus <> ? and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by pvid desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $pvid[$v['pvid']] = array($v['pvid'], $v['pvid']);
        }
    }
    return $pvid;
}
//20150509 get vid (暂时还没有把用 admin_var.php $vid 的统一过来)
function get_invoice_no(){
    $vid = '';
    if (isSysAdmin()){
        $rs = mysql_q('select vid from invoice where istatus <> ? order by vid desc', 'delete');
    }else{
        //20140325 队员开的单队长看不到，所以改了
        $rs = mysql_q('select vid from invoice where istatus <> ? and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by vid desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $vid[$v['vid']] = array($v['vid'], $v['vid']);
        }
    }
    return $vid;
}
//20130805 get cn_no
function get_credit_note_no(){
    $cn_no = '';
    if (isSysAdmin()){
        $rs = mysql_q('select cn_no from credit_note order by in_date desc');
    }else{
        //$rs = mysql_q('select cn_no from credit_note where cn_no in (select cn_no from credit_note where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by in_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        //20140325 队员开的单队长看不到，所以改了
        $rs = mysql_q('select cn_no from credit_note where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by in_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $cn_no[$v['cn_no']] = array($v['cn_no'], $v['cn_no']);
        }
    }
    return $cn_no;
}
//20130808 get new payment py_no for old payment
function get_py_no(){
    $py_no = '';
    if (isSysAdmin()){
        $rs = mysql_q('select py_no from payment_new order by in_date desc');
    }else{
        //$rs = mysql_q('select py_no from payment_new where py_no in (select py_no from payment_new where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by in_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        //20140325 队员开的单队长看不到，所以改了
        $rs = mysql_q('select py_no from payment_new where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by in_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $py_no[$v['py_no']] = array($v['py_no'], $v['py_no']);
        }
    }
    return $py_no;
}
//20130814 get $pcid
function get_pcid(){
    global $act;
    $pcid = '';
    if (isSysAdmin()){
        if(strpos($act, 'overheads') !== false || strpos($act, 'settlement') !== false){
            $rs = mysql_q('select pcid from purchase where istatus <> ? AND (istatus = ? OR istatus = ?) order by mark_date desc', 'delete', '(I)', '(S)');
        }elseif(strpos($act, 'qc_schedule') !== false){
            $rs = mysql_q('select pcid from purchase where istatus <> ? AND istatus <> ? AND istatus <> ? AND istatus <> ? order by mark_date desc', 'delete', '(C)', '(S)', '(D)');
        }else{
            $rs = mysql_q('select pcid from purchase where istatus <> ? order by mark_date desc', 'delete');
        }
    }else{
        if(strpos($act, 'overheads') !== false || strpos($act, 'settlement') !== false){
            $rs = mysql_q('select pcid from purchase where istatus <> ? AND (istatus = ? OR istatus = ?) AND created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup like ? or AdminName = ?) order by mark_date desc', 'delete', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }elseif(strpos($act, 'qc_schedule') !== false){
            $rs = mysql_q('select pcid from purchase where istatus <> ? AND istatus <> ? AND istatus <> ? AND istatus <> ? AND created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup like ? or AdminName = ?) order by mark_date desc', 'delete', '(C)', '(S)', '(D)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }else{
            $rs = mysql_q('select pcid from purchase where istatus <> ? AND created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup like ? or AdminName = ?) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }
    }

    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $pcid[$v['pcid']] = array($v['pcid'], $v['pcid']);
        }
    }
    return $pcid;
}
//20130815 get fty color
function get_fty_color($pcid){
    $fty_color = '';
    $rtn = mysql_qone('select sid from purchase where pcid = ?', $pcid);
    if( isset($rtn['sid']) && $rtn['sid'] != '' ){
        $color = fty_color($rtn['sid']);
        $fty_color = '<span style="font-weight:bold;color: '.$color[$rtn['sid']].'">'.$pcid.'</span>';
    }
    return $fty_color;
}
//20140218 get unit
function get_unit(){
    $unit = '';
    $rs = mysql_q('select * from unit');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $unit[$v['unit']] = array($v['unit'], $v['unit']);
        }
    }
    return $unit;
}
//20140303 用户整合到一个表后 获取用户
function get_user($user_type){
    $user = '';
    if (isSysAdmin()){
        $rs = mysql_q('select AdminName from tw_admin where AdminName <> ? and AdminEnabled = 1 and AdminPlatform like ?', 'ZJN', '%'.$user_type.'%');
    }else{
        //$rs = $mysql->q('select AdminName from tw_admin where AdminLuxGroup = (select AdminLuxGroup from tw_admin where AdminName = ?)', $_SESSION['logininfo']['aName']);
        $rs = mysql_q('select AdminName from tw_admin where (AdminLuxGroup like ? OR AdminName = ?) and AdminEnabled = 1 and  AdminPlatform like ?', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName'], '%'.$user_type.'%');
    }
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $user[$v['AdminName']] = array($v['AdminName'], $v['AdminName']);
        }
    }
    return $user;
}
//20140501
function get_supplier(){
    $supplier = '';
    $rs = mysql_q('select sid, name from supplier');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $supplier[$v['sid']] = array($v['name'], $v['sid']);
        }
    }
    return $supplier;
}
//20141212
function get_supplier_fty(){
    $supplier = '';
    if (isFtyAdmin()){
        $rs = mysql_q('select sid, name from supplier');
    }else{
        $rs = mysql_q('select sid, name from supplier where sid = ?', $_SESSION['ftylogininfo']['aFtyName']);
    }
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $supplier[$v['sid']] = array($v['name'], $v['sid']);
        }
    }
    return $supplier;
}
//20141216 检查是否所以po的item都出货了
function checkPurchaseItemIsOut($pcid){
    $rs = mysql_q('select pid, quantity from purchase_item where pcid = ?', $pcid);
    if($rs){
        $po_rtn =  mysql_fetch();
        foreach($po_rtn as $v){
            $delivery_item = mysql_qone('select sum(quantity) as total_qty from delivery_item where po_id = ? and p_id = ? group by p_id', $pcid, $v['pid']);
            if($delivery_item['total_qty'] < $v['quantity']){
                return false;
            }
        }
        return true;
    }else{
        return false;
    }
}
//20141216 改po的状态
function changePurchaseStatus($pcid, $status){
    global $ip_real;

    $po_rtn = mysql_qone('select istatus from purchase where pcid = ?', $pcid);
    if($status == '(S)'){
        if($po_rtn['istatus'] == '(D)' || $po_rtn['istatus'] == '(I)' || $po_rtn['istatus'] == ''){
            mysql_q('update purchase set istatus = ? where pcid = ?', '(S)', $pcid);
            mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS, $_SESSION["ftylogininfo"]["aName"]." add delivery change po status from ".$po_rtn['istatus']." to (S) in fty", ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_S, "", "", 0);

            return ' from '.$po_rtn['istatus'].' to (S)';
        }elseif($po_rtn['istatus'] == '(P)'){
            mysql_q('update purchase set istatus = ? where pcid = ?', '(C)', $pcid);
            mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS, $_SESSION["ftylogininfo"]["aName"]." add delivery change po status from ".$po_rtn['istatus']." to (C) in fty", ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_S, "", "", 0);

            return ' from '.$po_rtn['istatus'].' to (C)';
        }
    }elseif($status == 'Not-Out'){
        if($po_rtn['istatus'] == '(D)' || $po_rtn['istatus'] == '(I)' || $po_rtn['istatus'] == '(P)' || $po_rtn['istatus'] == ''){
            return '';
        }elseif($po_rtn['istatus'] == '(S)'){
            mysql_q('update purchase set istatus = ? where pcid = ?', '(I)', $pcid);
            mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS, $_SESSION["ftylogininfo"]["aName"]." add delivery change po status from ".$po_rtn['istatus']." to (I) in fty", ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_S, "", "", 0);

            return ' from '.$po_rtn['istatus'].' to (I)';
        }elseif($po_rtn['istatus'] == '(C)'){
            mysql_q('update purchase set istatus = ? where pcid = ?', '(P)', $pcid);
            mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS, $_SESSION["ftylogininfo"]["aName"]." add delivery change po status from ".$po_rtn['istatus']." to (P) in fty", ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_S, "", "", 0);

            return ' from '.$po_rtn['istatus'].' to (P)';
        }
    }
}
//20141216 读 currency 表内容，返回数组
function get_currency(){
    mysql_q('select * from currency');
    $currency = array();
    $rtn = mysql_fetch();
    foreach($rtn as $v){
        $currency[$v['type']] = $v['rate'];
    }
    return $currency;
}
//20150123 check packing list item里某invoice的item是否全部出货，先检查invoice，在检查proforma
//return 1 invoice (S) 但 proforma 没 (S)
//return 2 invoice和proforma都(S)
// !!!!! 注意：这里只是检查从invoice转为packing list的，不管从delivery转的
function check_packing_list_invoice_item($vid){
    //查invoice
    $rs = mysql_q('select item, sum(qty) as qty from packing_list_item where ref = ? group by item', $vid);
    if($rs){
        $pl_pi_rtn = mysql_fetch();

        //20150427 不改invoice状态
        /*$rs = mysql_q('select pid, sum(quantity) as qty from invoice_item where vid = ? group by pid', $vid);
        if($rs){
            $i_rtn = mysql_fetch();

            //以invoice为准,invoice在外层
            foreach($i_rtn as $w){
                foreach($pl_pi_rtn as $v){
                    if($v['item'] == $w['pid'] && $v['qty'] != $w['qty']){
                        return false;
                    }
                }
            }*/

            //查proforma
            $pvid = substr($vid, 0, 10);
            $rs = mysql_q('select item, sum(qty) as qty from packing_list_item where ref like ? group by item', '%'.$pvid.'%');
            if($rs){
                $pl_i_rtn = mysql_fetch();

                //invoice前10位为proforma，去除后面A、B、C等
                $rs = mysql_q('select pid, sum(quantity) as qty from proforma_item where pvid = ? and pid not like ? and pid not like ? group by pid', $pvid, 'TEMP%', 'temp%');
                if($rs){
                    $pi_rtn = mysql_fetch();

                    //以proforma为准,proforma在外层
                    foreach($pi_rtn as $w){
                        foreach($pl_i_rtn as $v){
                            if($v['item'] == $w['pid'] && $v['qty'] != $w['qty']){
                                //invoice (S) 但 proforma 没 (S)
                                return false;
                            }
                        }
                    }
                }
            }

            //invoice和proforma都(S)
            //return 2;

        /*}else{
            return false;
        }*/
    }else{
        return false;
    }
    return true;
}
//20150123 统一insert shipment record且跟新invoice status (S)的函数，packing list和shipment record都用这个
//status是1：改invoice(S)，2：改invoice(S)和proforma(S)
//20150427 已不用这个了
function insert_shipment_record($param, $status){

    //返回invoice状态转变的提示
    $i_change_status_hints = '';

    $pvid = substr($param['pi_no'], 0, 10);

    $rs = mysql_q('insert into shipment set pi_no = ?, awb = ?, s_date = ?, cost = ?, cost_remark = ?, s_status = ?, courier_or_forwarder = ?, pl_id = ?', $pvid, $param['awb'], $param['s_date'], $param['cost'], $param['cost_remark'], $param['s_status'], $param['courier_or_forwarder'], $param['pl_id']);

    if($rs){
        if($status == 1 || $status == 2){
            $rtn = mysql_qone('select istatus from invoice where vid = ?', $param['pi_no']);
            if($param['s_status'] == 'Complete'){
                if($rtn['istatus'] == '(I)'){
                    mysql_q('update invoice set istatus = ? where vid = ?', '(S)', $param['pi_no']);
                    $i_change_status_hints .= 'PI '. $param['pi_no'] . ' status change from (I) to (S); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_I_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_I_STATUS_S, "", "", 0);

                }elseif($rtn['istatus'] == '(P)'){
                    mysql_q('update invoice set istatus = ? where vid = ?', '(C)', $param['pi_no']);
                    $i_change_status_hints .= 'PI '. $param['pi_no'] . ' status change from (P) to (C); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_I_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_I_STATUS_S, "", "", 0);
                }
            }else{
                if($rtn['istatus'] == '(S)'){
                    mysql_q('update invoice set istatus = ? where vid = ?', '(I)', $param['pi_no']);
                    $i_change_status_hints .= 'PI '. $param['pi_no'] . ' status change from (S) to (I); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_I_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_I_STATUS_S, "", "", 0);
                }elseif($rtn['istatus'] == '(C)'){
                    mysql_q('update invoice set istatus = ? where vid = ?', '(P)', $param['pi_no']);
                    $i_change_status_hints .= 'PI '. $param['pi_no'] . ' status change from (C) to (P); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_I_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_I_STATUS_S, "", "", 0);
                }
            }

            if($status == 2){
                $rtn = mysql_qone('select istatus from proforma where pvid = ?', $pvid);
                if($rtn['istatus'] == '(I)'){
                    mysql_q('update proforma set istatus = ? where pvid = ?', '(S)', $pvid);
                    $i_change_status_hints .= 'PROFORMA ' . $pvid . ' status change from (I) to (S); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_PI_STATUS_S, "", "", 0);
                }elseif($rtn['istatus'] == '(P)'){
                    mysql_q('update proforma set istatus = ? where pvid = ?', '(C)', $pvid);
                    $i_change_status_hints .= 'PROFORMA ' . $pvid . ' status change from (P) to (C); ';

                    //add action log
                    mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], getIp()
                        , ACTION_LOG_SYS_DTP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$i_change_status_hints." in sys", ACTION_LOG_SYS_DTP_CHANGE_PI_STATUS_S, "", "", 0);
                }
            }
        }
    }
    return $i_change_status_hints;
}

//20150427
function add_shipment_record($param){
//返回invoice状态转变的提示
    $pi_change_status_hints = '';

    $pvid = substr($param['pi_no'], 0, 10);

    $rs = mysql_q('insert into shipment set pi_no = ?, awb = ?, s_date = ?, cost = ?, cost_remark = ?, s_status = ?, courier_or_forwarder = ?, pl_id = ?', $pvid, $param['awb'], $param['s_date'], $param['cost'], $param['cost_remark'], $param['s_status'], $param['courier_or_forwarder'], $param['pl_id']);
    if($rs){
        $rtn = mysql_qone('select istatus from proforma where pvid = ?', $pvid);

        if($param['s_status'] == 'Complete'){
            if($rtn['istatus'] == '(I)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(S)', $pvid);
                $pi_change_status_hints .= 'PI '. $pvid . ' status change from (I) to (S); ';

                //add action log
                mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], getIp()
                    , ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$pi_change_status_hints." in sys", ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_S, "", "", 0);

            }elseif($rtn['istatus'] == '(P)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(C)', $pvid);
                $pi_change_status_hints .= 'PI '. $pvid . ' status change from (P) to (C); ';

                //add action log
                mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], getIp()
                    , ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$pi_change_status_hints." in sys", ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_S, "", "", 0);
            }
        }elseif($param['s_status'] == 'Partial'){
            if($rtn['istatus'] == '(S)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(I)', $pvid);
                $pi_change_status_hints .= 'PI '. $pvid . ' status change from (S) to (I); ';

                //add action log
                mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], getIp()
                    , ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$pi_change_status_hints." in sys", ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_S, "", "", 0);
            }elseif($rtn['istatus'] == '(C)'){
                mysql_q('update proforma set istatus = ? where pvid = ?', '(P)', $pvid);
                $pi_change_status_hints .= 'PI '. $pvid . ' status change from (C) to (P); ';

                //add action log
                mysql_sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], getIp()
                    , ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS, $_SESSION["logininfo"]["aName"]." ".$pi_change_status_hints." in sys", ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_S, "", "", 0);
            }
        }
    }
    return $pi_change_status_hints;
}

//20150127
// !!!!! 注意：这里只是检查从delivery转为packing list的，不管从invoice转的
function check_packing_list_delivery_item($d_id){

    //先找出此出货单对应的所有PI和invoice，对应保存在一个数组里
    $d_arr = array();

    $rs = mysql_q('select po_id from delivery_item where d_id = ? group by po_id', $d_id);
    if($rs){
        $rtn = mysql_fetch();
        foreach($rtn as $v){

            $po_rtn = mysql_qone('select reference from purchase where pcid = ?', $v['po_id']);
            $d_arr[$d_id]['pi'][$po_rtn['reference']] = 1;

            //找跟此po有关联的所有出货单，但是这样，所有有关的出货单又不只是这个po的，所以还是不行！！！！
            //暂时先只按当前出货单转的packing list为准
            $rs = mysql_q('select item, sum(qty) as qty from packing_list_item where ref = ? group by item', $d_id);
            if($rs){
                $pl_rtn = mysql_fetch();

                //检查pi是否完成
                $rs = mysql_q('select pid, sum(quantity) as qty from proforma_item where pvid = ? and pid not like ? and pid not like ? group by pid', $po_rtn['reference'], 'TEMP%', 'temp%');
                if($rs){
                    $pi_rtn = mysql_fetch();

                    foreach($pi_rtn as $y){
                        foreach($pl_rtn as $x){
                            if($x['item'] == $y['pid'] && $x['qty'] != $y['qty']){
                                $d_arr[$d_id]['pi'][$po_rtn['reference']] = 0;
                                break;
                            }
                        }
                        if($d_arr[$d_id]['pi'][$po_rtn['reference']] == 0){
                            break;
                        }
                    }
                }

                /*$d_arr[$d_id]['invoice'][$po_rtn['reference']] = 1;

                //检查invoice是否完成，如果pi有拆分为多个invoice，则所有invoice完成了，才算invoice完成
                $rs = mysql_q('select pid, sum(quantity) as qty from invoice_item where vid like ? group by pid', '%'.$po_rtn['reference'].'%');
                if($rs){
                    $invoice_rtn = mysql_fetch();

                    foreach($invoice_rtn as $y){
                        foreach($pl_rtn as $x){
                            if($x['item'] == $y['pid'] && $x['qty'] != $y['qty']){
                                $d_arr[$d_id]['invoice'][$po_rtn['reference']] = 0;
                                break;
                            }
                        }
                        if($d_arr[$d_id]['invoice'][$po_rtn['reference']] == 0){
                            break;
                        }
                    }
                }*/
            }
        }
    }

    return $d_arr;
}
//20150411 1:全中文 2：显示中文用英文 3：全英文 4：显示英文用中文
function get_bom_lb($type){
    $bom_lb_c_c = array();
    $bom_lb_c_e = array();
    $bom_lb_e_e = array();
    $bom_lb_e_c = array();
    $rs = mysql_q('select bom_lb_name_chi, bom_lb_name_en from fty_bom_lb order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_lb_c_c[] = array($v['bom_lb_name_chi'], $v['bom_lb_name_chi']);
            $bom_lb_c_e[] = array($v['bom_lb_name_chi'], $v['bom_lb_name_en']);
            $bom_lb_e_e[] = array($v['bom_lb_name_en'], $v['bom_lb_name_en']);
            $bom_lb_e_c[] = array($v['bom_lb_name_en'], $v['bom_lb_name_chi']);
        }
    }

    if($type == 1){
        return $bom_lb_c_c;
    }elseif($type == 2){
        return $bom_lb_c_e;
    }elseif($type == 3){
        return $bom_lb_e_e;
    }elseif($type == 4){
        return $bom_lb_e_c;
    }
}
//20150411 1:全中文 2：显示中文值用英文 3：全英文
function get_bom_dcyl($type){
    $bom_dcyl_c_c = array();
    $bom_dcyl_c_e = array();
    $bom_dcyl_e_e = array();
    $rs = mysql_q('select bom_dcyl_name_chi, bom_dcyl_name_en from fty_bom_dcyl order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_dcyl_c_c[] = array($v['bom_dcyl_name_chi'], $v['bom_dcyl_name_chi']);
            $bom_dcyl_c_e[] = array($v['bom_dcyl_name_chi'], $v['bom_dcyl_name_en']);
            $bom_dcyl_e_e[] = array($v['bom_dcyl_name_en'], $v['bom_dcyl_name_en']);
        }
    }

    if($type == 1){
        return $bom_dcyl_c_c;
    }elseif($type == 2){
        return $bom_dcyl_c_e;
    }elseif($type == 3){
        return $bom_dcyl_e_e;
    }
}
//20150411 1:全中文 2：显示中文值用英文 3：全英文
function get_bom_bmcl($type){
    $bom_bmcl_c_c = array();
    $bom_bmcl_c_e = array();
    $bom_bmcl_e_e = array();
    $rs = mysql_q('select bom_bmcl_name_chi, bom_bmcl_name_en from fty_bom_bmcl order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_bmcl_c_c[] = array($v['bom_bmcl_name_chi'], $v['bom_bmcl_name_chi']);
            $bom_bmcl_c_e[] = array($v['bom_bmcl_name_chi'], $v['bom_bmcl_name_en']);
            $bom_bmcl_e_e[] = array($v['bom_bmcl_name_en'], $v['bom_bmcl_name_en']);
        }
    }

    if($type == 1){
        return $bom_bmcl_c_c;
    }elseif($type == 2){
        return $bom_bmcl_c_e;
    }elseif($type == 3){
        return $bom_bmcl_e_e;
    }
}
//20150411 1:全中文 2：显示中文值用英文 3：全英文
function get_bom_dd($type){
    $bom_dd_c_c = array();
    $bom_dd_c_e = array();
    $bom_dd_e_e = array();
    $rs = mysql_q('select bom_dd_name_chi, bom_dd_name_en from fty_bom_dd order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_dd_c_c[] = array($v['bom_dd_name_chi'], $v['bom_dd_name_chi']);
            $bom_dd_c_e[] = array($v['bom_dd_name_chi'], $v['bom_dd_name_en']);
            $bom_dd_e_e[] = array($v['bom_dd_name_en'], $v['bom_dd_name_en']);
        }
    }

    if($type == 1){
        return $bom_dd_c_c;
    }elseif($type == 2){
        return $bom_dd_c_e;
    }elseif($type == 3){
        return $bom_dd_e_e;
    }
}
//20150411 1:全中文 2：显示中文值用英文 3：全英文
function get_bom_ddhd($type){
    $bom_ddhd_c_c = array();
    $bom_ddhd_c_e = array();
    $bom_ddhd_e_e = array();
    $rs = mysql_q('select bom_ddhd_name_chi, bom_ddhd_name_en from fty_bom_ddhd order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_ddhd_c_c[] = array($v['bom_ddhd_name_chi'], $v['bom_ddhd_name_chi']);
            $bom_ddhd_c_e[] = array($v['bom_ddhd_name_chi'], $v['bom_ddhd_name_en']);
            $bom_ddhd_e_e[] = array($v['bom_ddhd_name_en'], $v['bom_ddhd_name_en']);
        }
    }

    if($type == 1){
        return $bom_ddhd_c_c;
    }elseif($type == 2){
        return $bom_ddhd_c_e;
    }elseif($type == 3){
        return $bom_ddhd_e_e;
    }
}
//20150411 1:全中文 2：显示中文值用英文 3：全英文
function get_bom_qt($type){
    $bom_qt_c_c = array();
    $bom_qt_c_e = array();
    $bom_qt_e_e = array();
    $rs = mysql_q('select bom_qt_name_chi, bom_qt_name_en from fty_bom_qt order by sort');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $bom_qt_c_c[] = array($v['bom_qt_name_chi'], $v['bom_qt_name_chi']);
            $bom_qt_c_e[] = array($v['bom_qt_name_chi'], $v['bom_qt_name_en']);
            $bom_qt_e_e[] = array($v['bom_qt_name_en'], $v['bom_qt_name_en']);
        }
    }

    if($type == 1){
        return $bom_qt_c_c;
    }elseif($type == 2){
        return $bom_qt_c_e;
    }elseif($type == 3){
        return $bom_qt_e_e;
    }
}
//組合send_to的內容，公司名+地址
function combineSendTo($cid, $sid, $contact_address){
    $send_to = '';
    if($cid != '' && $sid == ''){
        $rtn = mysql_qone('select name from customer where cid = ?', $cid);
    }elseif($cid == '' && $sid != ''){
        $rtn = mysql_qone('select name from supplier where sid = ?', $sid);
    }
    if($rtn){
        $send_to = $rtn['name'] . "\r\n" . $contact_address;
        /*
        $rtn = mysql_qone('select address from contact where name = ?', $contact_name);
        if($rtn){
            $send_to .= $rtn['address'];
        }
        */
        return $send_to;
    }
    return 'none';
}
function check_repeat_item($pid){
    $temp = array();
    $i = 0;
    foreach($pid as $v){
        if($i++ != 0){
            if(in_array($v, $temp)){
                //fb($v);
                return true;
            }
        }
        $temp[] = $v;
    }
    return false;
}
//20150701
function get_fty_jg_customer(){
    $customer = array();
    $rs = mysql_q('select cid, name from fty_jg_customer');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['name'], $v['cid']);
        }
    }
    return $customer;
}
function get_fty_wlgy_customer(){
    $customer = array();
    $rs = mysql_q('select cid, name from fty_wlgy_customer');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $customer[$v['cid']] = array($v['name'], $v['cid']);
        }
    }
    return $customer;
}
//20150706
function get_fty_task($pid){
    $fty_task = array();
    if($pid){
        $rs = mysql_q('select t_id, t_name from bom_task where bom_id = (select id from bom where g_id = ?)', $pid);
        if($rs){
            $rows = mysql_fetch();
            foreach($rows as $v){
                $fty_task[] = array($v['t_id'].':'.$v['t_name'], $v['t_id']);
            }
        }
        return $fty_task;
    }
}
//20160120
function get_fty_purchase(){
    $fty_purchase = array();
    if(isFtyAdmin()){
        $rs = mysql_q('select pcid from purchase where istatus = ? order by in_date desc', '(I)');
    }else{
        $rs = mysql_q('select pcid from purchase where sid = ? and istatus = ? order by in_date desc', $_SESSION['ftylogininfo']['aFtyName'], '(I)');
    }
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $fty_purchase[$v['pcid']] = array($v['pcid'], $v['pcid']);
        }
    }
    return $fty_purchase;
}
//20160121
function get_fty_t_type(){
    $t_type = array();
    if(isFtyAdmin()){
        $rs = mysql_q('select t_id, t_name from fty_task');
    }else{
        //$rs = $mysql->q('select t_id, t_name from fty_task where created_by in (select AdminName from tw_admin where FtyName = (select FtyName from tw_admin where AdminName = ?))', $_SESSION['ftylogininfo']['aName']);
        //20141202 上面的不知道怎么不行，没解决，暂时用下面的
        //$rs = $mysql->q('select t_id, t_name from fty_task where created_by = ?', $_SESSION['ftylogininfo']['aName']);
        //20150102 用这个才行，不知道最上面的为什么不行
        $rs = mysql_q('SELECT f.t_id, f.t_name FROM fty_task f, tw_admin a WHERE f.created_by = a.AdminName AND a.FtyName = (SELECT FtyName FROM tw_admin WHERE AdminName = ?)', $_SESSION['ftylogininfo']['aName']);

    }
    if($rs){
        $rtn = mysql_fetch();
        foreach($rtn as $v){
            $t_type[] = array($v['t_id'].' : '.$v['t_name'], $v['t_id']);
        }
    }
    return $t_type;
}
function get_fty_client_company(){
    $client_company = array();
    if(isFtyAdmin()){
        $rs = mysql_q('select company from fty_client');
    }else{
        //默认都加一个本公司到工厂的客户名单去
        $rs = mysql_q('select company from fty_client where created_by = ? or created_by = ?', $_SESSION['ftylogininfo']['aName'], 'all');
    }
    if($rs){
        $rtn = mysql_fetch();
        foreach($rtn as $v){
            $client_company[] = array($v['company'], $v['company']);
        }
    }
    return $client_company;
}
function get_sys_group(){
    $group = array();
    //20141027 只选出ERP用户，其他用户不具备group功能
    $rs = mysql_q('select AdminName from tw_admin where AdminName <> ? and AdminName <> ? and AdminPlatform like ?', 'ZJN', 'KEVIN', '%sys%');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $group[] = array($v['AdminName'], $v['AdminName']);
        }
    }
    return $group;
}
//20160717
function get_it_request_location(){
    $location = array();
    $rs = mysql_q('select wh_name_chi from warehouse');
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $location[] = array($v['wh_name_chi'], $v['wh_name_chi']);
        }
    }
    return $location;
}
//20161030 工厂物料库存修改
function changeFtyMaterialNum($m_id, $num){
	mysql_q('INSERT INTO fty_material_warehouse set m_id = ?, m_num = ? ON DUPLICATE KEY UPDATE m_num = m_num + ?', $m_id, $num, $num);
}
function delWarehouseMaterial($table, $value){
	if($table == 'fty_material_buy_item'){
		$rs = mysql_q('select m_id, m_value from fty_material_buy_item where main_id = ?', $value);
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				changeFtyMaterialNum($v['m_id'], -$v['m_value']);
			}
		}
	}elseif($table == 'fty_material_in_item'){
		$rs = mysql_q('select mi_id, mi_value from fty_material_in_item where main_id = ?', $value);
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				changeFtyMaterialNum($v['mi_id'], -$v['mi_value']);
			}
		}
	}elseif($table == 'fty_material_out_item'){
		$rs = mysql_q('select mo_id, mo_value from fty_material_out_item where main_id = ?', $value);
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				changeFtyMaterialNum($v['mo_id'], $v['mo_value']);
			}
		}
	}
}
function changeProductSetting($pid){
    $rtn = mysql_qone('select pid from setting');
    $pid = substr($pid, 1, 6);
    if($pid >= $rtn['pid']){
        mysql_q('update setting set pid = pid + 1');
    }
}
function getTheme(){
    $rs = mysql_q('select id, theme from theme order by id desc');
    $theme = array();
    if($rs){
        $rtn = mysql_fetch();
        foreach($rtn as $v){
            $theme[] = array($v['theme'], $v['id']);
        }
    }
    return $theme;
}
function getSupplier(){
    $rs = mysql_q('select sid, name from supplier');
    $supplier = array();
    if($rs){
        $rows = mysql_fetch();
        foreach($rows as $v){
            $supplier[$v['sid']] = array($v['name'], $v['sid']);
        }
    }
    return $supplier;
}

/*************************************************
 * 处理逻辑
 */
function handleFtyCustomerAp($type, $cid, $action, $ap){
    if ($type == 1) {
        if ($action == 1) {
            mysql_q('update fty_wlgy_customer set ap=ap+? where cid=?', $ap, $cid);
        } elseif ($action == 2) {
            mysql_q('update fty_wlgy_customer set ap=ap-? where cid=?', $ap, $cid);
        }
    } elseif ($type == 2) {
        if ($action == 1) {
            mysql_q('update fty_jg_customer set ap=ap+? where cid = ?', $ap, $cid);
        } elseif ($action == 2) {
            mysql_q('update fty_jg_customer set ap=ap-? where cid = ?', $ap, $cid);
        }
    }
}