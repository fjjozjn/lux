<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//checkAdminAllowedIP();

$form = new My_Forms();
$formItems = array(
		'account' => array('type' => 'text', 'minlen' => 5, 'maxlen' => 15, 'required' => 1, 'restrict' => 'account'),
		'password' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15, 'required' => 1),
		'logincode' => array('type' => 'text', 'minlen' => 4, 'required' => 1, 'restrict' => 'scode', 'info' => 'Click the image for refresh. Passwords are NOT case sensitive'),
		'submitbtn' => array('type' => 'submit', 'value' => ' Submit '),
		);
$form->init($formItems);

if(!$myerror->getAny() && $form->check()){
	//20130206
	//重新get客户端的IP
    //20140208 将sys、fty、luxcraft的用户整合到tw_admin里，加标志AdminPlatform
    $info = $mysql->sp('CALL admin_check_login(?, ?, ?, ?)', $_POST['account'], md5(ADMIN_PREFIX.$_POST['password']. ADMIN_POSTFIX), getIp(), '%luxcraft%');
	if(!$info){
		//add failure log
		$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
				, 0, $ip_real
				, ADMIN_CATG_LUXCRAFT_LOGIN, $_POST['account']."登入零售管理系統失败", ADMIN_ACTION_LUXCRAFT_LOGIN_FAILURE, "", "", 0);
		$myerror->warn('登入失败，请填写正确的用户名和密码');
	}else{
        $_SESSION['luxcraftlogininfo'] = $mysql->fetch(1); //get the login user information
        //print_r_pre($_SESSION['ftylogininfo']);
        //add success log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
            , ADMIN_CATG_LUXCRAFT_LOGIN, $_SESSION["luxcraftlogininfo"]["aName"]."成功登入零售管理系統", ADMIN_ACTION_LUXCRAFT_LOGIN_SUCCESS, "", "", 0);
        $myerror->ok('Success login '.SITE_NAME.'!', 'main');
    }
}

?>

<table width="500" border="0" align="center" cellpadding="5" cellspacing="5">
<tr>
  <td>
<?

if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');
}
if($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}else{

	$form->begin();
?>
<table width="100%" border="0" cellpadding="5" cellspacing="5">
  <tr>
    <td colspan="2" align="center">&nbsp;</td>
  </tr>
  <tr>
    <td width="120" align="right" valign="top">Acount :&nbsp;</td>
    <td height="35" valign="top"><?php
   $form->show('account');
	?></td>
  </tr>
  <tr>
    <td align="right" valign="top">Password :&nbsp;</td>
    <td height="35" valign="top"><?php
   $form->show('password');
	?></td>
  </tr>
  <tr>
    <td align="right" valign="top">Verify Code :&nbsp;</td>
    <td height="35" valign="top"><?php
   $form->show('logincode');
	?>
  </td>
  </tr>
  <tr>
    <td height="35">&nbsp;</td>
    <td><?php $form->show('submitbtn'); ?></td>
  </tr>
</table>
<?php
	$form->end();

}
?>
</td></tr></table>

<script>
    //20130402 改变login按钮位置
    $(".buttonfield").css("margin-left", "0px")
</script>