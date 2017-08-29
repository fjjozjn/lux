<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

define('ADMIN_CATG_LUXCRAFT_LOGIN', 'luxcraft_login');
define('ADMIN_CATG_LUXCRAFT_LOGOUT', 'luxcraft_logout');
define('ADMIN_ACTION_LUXCRAFT_LOGIN_SUCCESS', '1');
define('ADMIN_ACTION_LUXCRAFT_LOGIN_FAILURE', '-1');
define('ADMIN_ACTION_LUXCRAFT_LOGOUT_SUCCESS', '2');
//登出时只unset session ，但是unset没有返回值，无法判断是否失败，只能认为都是成功的了
//define('ADMIN_ACTION_FTY_LOGOUT_FAILURE', '-2');


