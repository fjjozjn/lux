<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//checkAdminAllowedIP();

$form = new My_Forms();
$formItems = array(
		'account' => array('type' => 'text', 'minlen' => 5, 'maxlen' => 15, 'required' => 1, 'restrict' => 'account'),
		'password' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15, 'required' => 1),
		'logincode' => array('type' => 'text', 'minlen' => 4, 'required' => 1, 'restrict' => 'scode', 'info' => '点击图片可刷新，不区分大小写'),
		'submitbtn' => array('type' => 'submit', 'value' => ' 确定 '),
		'contact_us' => array('type' => 'button', 'value' => ' 注册 ', 'addon' => 'style="margin-left:100px"'),
		);
$form->init($formItems);

if(!$myerror->getAny() && $form->check()){
	//20130206 mod 去掉首次登入修改密码的设定，改为让新用户自己注册帐号
	//$rtn = $mysql->qone('select first_login from fty_user where FtyLogin = ?', $_POST['account']);
	//重新get客户端的IP
    //20140208 将sys、fty、luxcraft的用户整合到tw_admin里，加标志AdminPlatform
    $info = $mysql->sp('CALL admin_check_login(?, ?, ?, ?)', $_POST['account'], md5(ADMIN_PREFIX.$_POST['password']. ADMIN_POSTFIX), getIp(), '%fty%');
	if(!$info){
		//add failure log
		$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
				, 0, $ip_real
				, ADMIN_CATG_FTY_LOGIN, $_POST['account']."登入工厂后台失败", ADMIN_ACTION_FTY_LOGIN_FAILURE, "", "", 0);		
		$myerror->warn('登入失败，请填写正确的用户名和密码');
	}else{
		/*
		if($rtn['first_login'] == 0){
			//已这个session来达到用户重置密码的功能
			$_SESSION['resetpassword'] = $mysql->fetch(1);
			if(isset($_SESSION['resetpassword'])){
				echo "<script>location.href='?act=index';</script>";
				//$myerror->ok('您已经成功登入 '.SITE_NAME.'，请重置您的密码!', 'index');
			}
		}else{
			*/
			$_SESSION['ftylogininfo'] = $mysql->fetch(1); //get the login user information
			//print_r_pre($_SESSION['ftylogininfo']);
			//add success log
			$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
					, $_SESSION['ftylogininfo']['aID'], $ip_real
					, ADMIN_CATG_FTY_LOGIN, $_SESSION["ftylogininfo"]["aName"]."成功登入工厂后台", ADMIN_ACTION_FTY_LOGIN_SUCCESS, "", "", 0);
			$myerror->ok('您已经成功登入 '.SITE_NAME.'!', 'main');
		//}
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
    <td width="120" align="right" valign="top">帐号：</td>
    <td height="35" valign="top"><?php
   $form->show('account');
	?></td>
  </tr>
  <tr>
    <td align="right" valign="top">密码：</td>
    <td height="35" valign="top"><?php
   $form->show('password');
	?></td>
  </tr>
  <tr>
    <td align="right" valign="top">验证码：</td>
    <td height="35" valign="top"><?php
   $form->show('logincode');
	?>
  </td>
  </tr>
  <tr>
    <td height="35">&nbsp;</td>
    <td><?php $form->show('submitbtn'); ?><?php $form->show('contact_us'); ?></td>
  </tr>
</table>
<?php
	$form->end();

}
?>
</td></tr></table>

<script>
$("#contact_us").click(function(){
	goto('?act=register');
})
//20130213 改变登入和注册按钮的位置
$(".buttonfield").css("margin-left", "0px")
</script>