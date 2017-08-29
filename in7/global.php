<?php
/*
change log

2011.03.01		修改isMt，不再=2。修改判断维护的SP，直接返回系统是否维护中。
2011.06.15		提前获取IP，当IP来自深港开发IP，并且带有合法的后台session时，显示debug资料
2011.06.24		前置PHP版本判断代码，去掉SHOW_DEBUG

*/
//20121222
/*
header('Content-type: text/html; charset=utf-8');
echo '系统地址已更新，点击-><a href="http://223.197.254.157/sys/"> 这里 </a>';die();
*/
// 运行系统最低条件
if(PHP_VERSION < '5.3.0'){
	die('System requirment: Higher PHP Version');
}elseif(!function_exists('mysqli_connect')){
	die('System requirment: No SQL Component');
}

//20130723 改为0，因为怕有的pi item太多，再加上网速慢，要保存需要很长时间
set_time_limit(0);										//设定超时，默认30秒，不够长
//20121029 将网页内容利用 gzip 压缩后再输出，减小页面大小
//20121212 网上说gzip在windows下有的页面不能用，现在出现的是quotation_pdf_photo_price.php 这个页面无法显示了（现在的做法是到这个页面关闭了gzip），如果还出现了，就先关掉，等程序移到linux下再开
//ob_start('ob_gzhandler');								//打开缓存
ob_start();											//打开缓存
//date_default_timezone_set('Asia/ShangHai'); 			//设定时区
//20121212 Linux没法用上面的
date_default_timezone_set('PRC'); 			//设定时区
define('ROOT_DIR',		substr(__DIR__, 0, -3));		//获取网站根目录的绝对路径
define('BEEN_INCLUDE',	true);							//为被包含文件检查是否被global.php包含设定初始值
define('TIME', time());									//设定当时时间戳常量

//--------------禁止浏览器cache---------------------
//header("ETag: PUB" . TIME);
//20130816 去掉下面四行，去掉禁止浏览器缓存，因为现在的product图片都用时间戳命名了，不怕旧图片因同名被缓存了，试试去掉会不会提高速度
/*header("Last-Modified: " . gmdate("D, d M Y H:i:s", TIME-10) . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", TIME + 5) . " GMT");
header("Pragma: no-cache");
header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");*/
header('Content-type: text/html; charset=utf-8');
// session_cache_limiter("nocache");

if(session_id() == '') {								//打开session
	session_start();
}

// 先读IP，设置调试开关
require(ROOT_DIR.'in7/var.inc.php'); //一些数值&变量设定
require(ROOT_DIR.'in7/function.php'); //实用函数
require(ROOT_DIR.'in7/fun.user.php'); //与session及用户有关的函数

// 设置调试开关
$ip_real = getIp();
$ip = '192.168.1.25';//因为只能指定的内网IP访问，所以就指定了一个内网IP
//是否显示php错误信息 & FireBug调试信息
//mod by zjn 20120413 为了fty能够显示调试信息，先把这里的session判断注释掉了，fty发布了后，要恢复这里，因为fty是不显示调试信息的
!defined('SHOW_ERROR') && define('SHOW_ERROR', (in_array($ip, $alwaysDebugIpList) /*&& isset($_SESSION['logininfo']) && is_array($_SESSION['logininfo'])*/) ? true : false);

//20131219 改IS_DIST 1 为 0 ，不知道过了很久没点网站css样式失效是不是这个原因，先试一下
//20131219 fty 样式有问题，又改为1了
//20131223 js和css文件都统一放在更目录的ui下了，修改了sys,fty,luxcraft的page.php和 model/index.php的引用js和css路径后，IS_DIST又改为0了
!defined('IS_DIST') && define('IS_DIST', 0);			//网站是否已发布，用来显示合併后的JS、CSS，减少HTML并发连接数
define('DEL_HTML',		true);							//用户输入的信息是否html过滤

//打开或关闭错误输出
if(/*SHOW_ERROR*/true){
	//ini_set("soap.wsdl_cache_enabled", 0);			//use for php.soap, now it's not available.
	ini_set('display_errors', true);
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
	
	require(ROOT_DIR.'class/FB/fb.php');				//打开FireBug
	require(ROOT_DIR.'class/ChromePhp.php');			//打开Chromephp （还不知道怎么用）
}else{
	ini_set('display_errors', false);
	error_reporting(0);
}

mb_internal_encoding('UTF-8'); 							//多字节处理设UTF8
$isMt = false;											//系统默认为非维护状态

/* ----------    函数 ----------- */
require(ROOT_DIR.'in7/config/sso.php');					//SSO配置
require(ROOT_DIR.'in7/security.php'); 					//SQL安全

//禁止 register_globals
//主机php.ini设定好，不必做此操作
//unregister_GLOBALS();

/* --------------- 初始  class ---------------*/

require(ROOT_DIR.'class/My/Errors.php');				//错误处理
require(ROOT_DIR.'class/My/Encrypt.php');				//加解密
require(ROOT_DIR.'class/My/Mysql.php');					//mysql class
require(ROOT_DIR.'class/My/Forms.php');					//自动表单验证
require(ROOT_DIR.'class/My/Scode.php');					//产生并验证 验证码
require(ROOT_DIR.'class/My/SSOServer.php');				//SSO登录

/* ----------------运算---------------------*/
if(!$ip){												//未取到IP时报错，正常不会发生此情况
	$myerror->error('访问页面时出现错误，请返回首页。(0x0914)', 'INDEX');
}

//读取,检查,初始常用的REQUEST值
$act			= getRequest('act');
$theid			= getRequest('theid');
$page			= getRequest('page');
$ajax			= getRequest('ajax');

if($theid !== false && !isId($theid)){					//如果有theid值但却不是数字, 则终止页面
	$myerror->error('缺少必要的参数，页面显示失败，请重试');
}
$page = (!$page || !isId($page) || intval($page) < 1) ? 1 : intval($page);

// 记录来自外站的Referer URL
//mod 20120919 因出现了 Notice: Unknown: Skipping numeric key 3960229 in Unknown on line 0 这个错误，所以去掉，大概是因为$_SESSION 使用了数字键名
/*
if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $goDomain) === false){
	userSession('refer', $_SERVER['HTTP_REFERER']);
}
*/
/*
//连接游戏数据库
$cc = mssql_connect($goMssqlDbInfo['host'], $goMssqlDbInfo['user'], $goMssqlDbInfo['passwd']) or die('<H2>Connect Game Server Failed</H2>');
mssql_select_db($goMssqlDbInfo['database'], $cc) or die('<H2>Server Failed.</H2>');
$goMssqlDbInfo = NULL;
*/
if(!$myerror->getError()){
	//无错误才连接mysql
	//fb($goDbInfo);
	$mysql = new My_Mysql($goDbInfo);
	/*
	if(!$myerror->getError()){
		//本系统暂不需要中断维护
		$rs = $mysql->sp('CALL check_maintain()');
		if($rs){
			$isMt = true;
		}
	}
	*/
	
	//20121217 记录每个用户每时每刻的浏览记录，以便考勤
	//登入前没有session，所以加了@
	/*
	$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
		, @$_SESSION['logininfo']['aID'], $ip_real
		, 'view', @$_SESSION["logininfo"]["aName"].' http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"], 1, "", "", 0);
		*/
}
?>

