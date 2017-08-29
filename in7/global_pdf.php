<?
//20121224 生成pdf都出错了，出现的是TCPDF ERROR: Some data has already been output, can't send PDF file，怀疑是ob_start造成的，由于没时间查是哪里出错，于是重新写了个只用于pdf的global文件
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if(session_id() == '') {								//打开session
	session_start();
}
header('Content-type: text/html; charset=utf-8');
define('ROOT_DIR',		substr(__DIR__, 0, -3));		//获取网站根目录的绝对路径
define('BEEN_INCLUDE',	true);							//为被包含文件检查是否被global.php包含设定初始值
ini_set('display_errors', true);
//error_reporting(E_ALL & ~E_DEPRECATED);						
require(ROOT_DIR.'class/FB/fb.php');                    //FireBug
require(ROOT_DIR.'in7/var.inc.php'); //一些数值&变量设定
require(ROOT_DIR.'in7/function.php'); //实用函数
require(ROOT_DIR.'class/My/Errors.php');				//错误处理
require(ROOT_DIR.'class/My/Mysql.php');					//mysql class

if(!$myerror->getError()){
	//无错误才连接mysql
	//fb($goDbInfo);
	$mysql = new My_Mysql($goDbInfo);
}