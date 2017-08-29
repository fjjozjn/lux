<?php
/*
changelog

2010-05-25	userSession() 加入了 $_SESSION[$sid]['noLoginPage']，以便于用户在登录后，回到他之前被提示要求登录的页面上。
2010-12-06	userSession() 如果UID为false, 则清除SSO COOKIE
2010-12-14	加入ipRestrictSimple()

*/


/*
 * 与用户session有关的操作
 * $koa :		Key Or Array的简写。它的值可以是以下几种：
 				FULL / LOGOUT / array / string
			详细：
 				FULL	返回全部用户session数据
				LOGOUT	用户登出，销毁session
				array	接受用户session值，此值可以是任何需要临时保存的用户值（方便集中管理，不必随时另开一个session）
				string	返回此key的value，value存在并且非empty则返回value，否则返回false
 * $setValue	值为默认时，取值
 				任何其他值，赋值至$koa所指定的key
 */
function userSession($koa = 'UID', $setValue = 'noSetValue'){
	if ( session_id() === '' ) {
		session_start();
	}
	$sid = substr(session_id(), 4, 7);
	if($koa == 'UID'){
		// 考虑到UID的高使用频率，单独拿到最前面做单独处理.
		if(isset($_SESSION[$sid][$koa]) && $_SESSION[$sid][$koa]){
			return $_SESSION[$sid][$koa];
		}else{
			//unset($_SESSION[$sid]);
			if (isset($_COOKIE[$GLOBALS['ssoCookieName']])) {
				setcookie($GLOBALS['ssoCookieName'], '', time()-42000, '/', $GLOBALS['ssoCookieDomain']);
			}
			//if(isset($GLOBALS['act']) && !in_array($GLOBALS['act'], array('index', 'error', 'main', 'construct', 'member-login', 'member-logout', 'member-signin'))){
				//$_SESSION[$sid]['noLoginPage'] = getLocation();
			//}
			return false;
		}
	}elseif(is_array($koa)){
		//清除密码资料，session中不需要保存密码
		if(set($koa['GOPassword'])) $koa['GOPassword'] = '';
		if(isset($_SESSION[$sid]) && is_array($_SESSION[$sid])){
			$_SESSION[$sid] = array_merge($_SESSION[$sid], $koa);
		}else{
			$_SESSION[$sid] = $koa;
		}
	}elseif($koa == 'FULL'){
		return $_SESSION[$sid];
	}elseif($koa == 'LOGOUT'){
		unset($_SESSION[$sid]);
	}else{
		if($setValue !== 'noSetValue'){
			//向 session 中写入 Key 值
			$_SESSION[$sid][$koa] = $setValue;
			return $_SESSION[$sid][$koa];
		}else{
			//读取 session 中的 Key 值
			if(isset($_SESSION[$sid][$koa])){
				return $_SESSION[$sid][$koa];
			}else{
				return false;
			}
		}
	}
}

/*
 * 根据IP限制用户访问
 * 或者日期是否大于某时间线，合乎条件即返回，否则不显示测试页面
 */
function ipRestrict($openDate = 0, $msg = '', $ipArray = array()){
	global $ip, $TestUserIp;
	if(!$ipArray) $ipArray = $TestUserIp;
	if(!$ipArray || in_array($ip, $ipArray) || ($openDate && strtotime($openDate) < TIME)){
		return true;
	}else{
		//$GLOBALS['myerror']->info($TestUserIp);
		//$GLOBALS['myerror']->info($ip);
		$GLOBALS['myerror']->error('此服务暂未开放，请稍后再试'. ($msg ? '<br />'. $msg : ''), 'BACK');
		return false;
	}
}

// 获取用户IP
// 优先 HTTP_CLIENT_IP > REMOTE_ADDR > HTTP_X_FORWARDED_FOR.
// 返回 ''表示未能取到合法ip
function getIp(){
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
		if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] && validIp($_SERVER['HTTP_CLIENT_IP'])) {
			$proxy = $_SERVER['HTTP_CLIENT_IP'];
		}elseif($_SERVER['REMOTE_ADDR'] && validIp($_SERVER['REMOTE_ADDR'])){
			$proxy = $_SERVER['REMOTE_ADDR'];
		}else{
			$proxy = '';
		}
		if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false){
			list($ip, $others) = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'], 2);
			$ip = trim($ip);
		}else{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		// 如果 REMOTE_ADDR 有值，或者HTTP_X_FORWARDED_FOR 取到值不合法，都将IP设为REMOTE_ADDR
		if($proxy){
			$ip = $proxy;
		}elseif(!validIp($ip)){
			$ip = '';
		}	
	} else {
		if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] && validIp($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif($_SERVER['REMOTE_ADDR'] && validIp($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else{
			$ip = '';
		}
	}
	return $ip;
}

// 检查用户是否来自被ban的ip
//参数: $ban 存有被ban IP列表的文字文件
// 返回: true表示被ban, false表示是正常ip
function banIp($ip, $ban){
	@$f = getContentByLine($ban);
	if($f){
		$first_ip	= substr($ip, 0, strpos($ip, '.'));	// 192
		$short_ip	= substr($ip, 0, strpos($ip, '.', 4));	// 192.168
		$long_ip	= substr($ip, 0, strrpos($ip, '.'));	// 192.168.100
		foreach($f as $value){
			if($value == $first_ip || $value == $short_ip || $value == $long_ip || $value == $ip)
				return true;
		}
	}
	return false;
}

function validIp($ip){
	if(!$ip) return false;
	if($ip == '127.0.0.1') return false;
	$iplist = explode('.', substr($ip, 0, 15));
	if(count($iplist) != 4) return false;
	for($i = 0; $i < 4; $i++){
		if(!isId($iplist[$i])){
			return false;
		}else{
			$j = intval($iplist[$i]);
		}
		if(($i == 0 && $j == 10) || ($i == 1 && (($j == 192 && $j == 168) || ($j == 172 && $j > 15 && $j < 32)))) return false;
	}
	return true;
}


// 针对当前会话，统计前后两次使用本函数的时间差
// 避免网页被快速刷新
function timeWait($t = 0, $forWhat = 'retry'){
	if(!$t) $t = $GLOBALS['timeShouldWait'];
	$time = time();
	$last = userSession($forWhat);
	userSession($forWhat, $time);
	if($last && ($time - $last) < $t){
		return false;
	}else{
		return true;
	}
}

// 至少n秒刷新一次用户在线状态
/* 2010-12-03 修改sp，不再删除20分钟内未登录的用户Session，改为删除6小时前的Session */
function updateSession($second = 60){
	if(timeWait($second, 'sessionFresh') || (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == MYCARD_END_PAGE)){
		mysql_sp('CALL member_update_session(?, ?, ?, ?, ?)', userSession(), userSession('GOLogin'), dateMore(), $GLOBALS['ip'], md5($_SERVER['HTTP_USER_AGENT']));
		/*
		$rtn = mysql_sp('CALL member_info_byid(?)', userSession());
		if($rtn){
			$info = mysql_fetch(1);
			userSession($info);
		}
		*/
	}
}

function mustLogin(){
	$GLOBALS['myerror']->error('您没有登入 台湾 Gameone 游戏平台，无法访问本页面。', 'member-login');
	userSession('noLoginPage', getLocation());
}

/*
 * 根据IP限制用户访问
 */
function ipRestrictSimple(){
	global $ip, $TestUserIp;
	if(!$TestUserIp || !in_array($ip, $TestUserIp)){
		return false;
	}else{
		return true;
	}
}
?>