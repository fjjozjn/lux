<?

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
define('SITE_NAME', '樂思供應鏈管理系統');
define('SITE_VER', '');

//specify functions for admin system
require_once('admin_function.php');

//specify constant and value for admin system
require_once('admin_var.php');

//table listing class
require_once('recordset.class3.php');

define('ADMIN_PREFIX', 'jdf932n');
define('ADMIN_POSTFIX', 'sn206yf');

define('ADMIN_ROW_PER_PAGE', 10);

// mod zjn 跳出這些頁面則清空session，也就是上傳圖片就白傳了，所以要重新上傳圖片，相同名字的也會覆蓋之前的，應該沒事吧這樣
if( strpos($act, 'ajax-') === false && !in_array($act, array('upload_photo', 'sendform', 'modifyform', 'addproduct', 'modifyproduct', 'upload_photo_mod'))){
	$_SESSION['fty_upload_photo'] = '';
	$_SESSION['fty_upload_photo_add'] = '';
	$_SESSION['fty_upload_photo_mod'] = '';
	fb('clear session!');
}