<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
define('SITE_NAME', 'LUX ERP SYSTEM');
define('SITE_VER', '');

//specify functions for admin system
require_once('admin_function.php');

//specify constant and value for admin system
require_once('admin_var.php');

//table listing class
require_once('recordset.class.php');

//腳本運行, 3min超時
// set_time_limit(35);
// define('BEEN_INCLUDE',	TRUE); //為被包含文件檢查是否被global.php包含設定初始值

//MYSQL database user setting
// define('DATABASE_SERVER', '127.0.0.1');
// define('DATABASE_USER', 'root');
// define('DATABASE_PWD', '1qaz2wsx');
// define('DATABASE_DB', 'GOlogs_new');

define('ADMIN_PREFIX', 'jdf932n');
define('ADMIN_POSTFIX', 'sn206yf');

define('ADMIN_ROW_PER_PAGE', 10);

//for external access to check mycard
define('MYCARD_LOGIN', 'softworld2831');
define('MYCARD_PW', '2ida6gm49f23');

// mod zjn 跳出這些頁面則清空session，也就是上傳圖片就白傳了，所以要重新上傳圖片，相同名字的也會覆蓋之前的，應該沒事吧這樣
// 20120925 要非常注意，访问ajax页面也会进来清空session
if( strpos($act, 'ajax-') === false && !in_array($act, array('upload_photo', 'sendform', 'modifyform', 'com-addproduct', 'com-modifyproduct', 'com-upload_photo_mod', 'com-delete_photo'))){
	//fb('go into here to clear photo session!!!');
	//20130321 use unset
	unset($_SESSION['upload_photo']);
	unset($_SESSION['upload_photo_add']);
	unset($_SESSION['upload_photo_mod']);
	unset($_SESSION['copyid']);
}

//跳转出除了ajax的页面则清空 currency 的 session
//20130705 不再使用customer 和currency 的session 了，改为直接jquery取页面selectbox的值
/*if( strpos($act, 'ajax') === false){
	unset($_SESSION['currency']);
	//20121023
	unset($_SESSION['customer']);
}*/

// define("DEFAULT_LANG", "tc"); //tc => traditional chinese, sc => simpfied chinese

// only the IP inside the array will be allowed to access system
// no IP input = no constraint
// $allow_ip = array(
	// "127.0.0.1"
// );

//session_cache_expire(1); //it's not work this way.
//set execution time limit
// set_time_limit(120);

