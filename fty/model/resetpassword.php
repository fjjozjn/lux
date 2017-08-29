<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//checkAdminAllowedIP();
$form = new My_Forms();
$formItems = array(
		'account' => array('type' => 'text', 'minlen' => 5, 'maxlen' => 15, 'required' => 1, 'restrict' => 'account', 'value' => $_SESSION['resetpassword']['aLogin'], 'readonly' => 'readonly'),
		'new_password' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15, 'info' => '● 为了您的帐号安全，请修改默认密码<br />● 请输入新密码', 'required' => 1),
		're_new_password' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15, 'info' => '● 再输入一次新密码', 'compare' => 'new_password', 'required' => 1),				
		'submitbtn' => array('type' => 'submit', 'value' => ' 确定 '),
		);
$form->init($formItems);

if(!$myerror->getAny() && $form->check()){

	$rtn = $mysql->q('update fty_user set FtyPassword = ?, first_login = ? where FtyLogin = ?', md5(ADMIN_PREFIX.$_POST['new_password']. ADMIN_POSTFIX), 1, $_SESSION['resetpassword']['aLogin']);
	if($rtn){
		//重新get客户端的IP
		$info = $mysql->sp('CALL fty_user_check_login(?, ?, ?)', $_SESSION['resetpassword']['aLogin'], md5(ADMIN_PREFIX.$_POST['new_password']. ADMIN_POSTFIX), getIp());	
		if(!$info){
			//add failure log
			$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
					, 0, $ip_real
					, 6, $_POST['account']."重置密码，无法登入后台", 17, "", "", 0);		
			$myerror->warn('登入失败，请填写正确的用户名和密码，并检查新密码格式是否正确');
		}else{
			$info = $mysql->sp('CALL fty_user_check_login(?, ?, ?)', $_SESSION['resetpassword']['aLogin'], md5(ADMIN_PREFIX.$_POST['new_password']. ADMIN_POSTFIX), getIp());	
			if($info){
				$_SESSION['ftylogininfo'] = $mysql->fetch(1); //get the login user information
				
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['resetpassword']['aID'], $ip_real
						, 6,$_SESSION['resetpassword']['aName']."成功重置密码", 16, "", "", 0);
						
				unset($_SESSION['resetpassword']);
					
				$myerror->ok('您已经成功重置密码! 点击继续进入系统', 'index');	
			}else{
				$myerror->warn('重置密码失败!', 'index');	
			}
		}
	}else{
		$myerror->warn('重置密码失败!', 'index');
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
    <td align="right" valign="top" colspan="2">&nbsp;</td>
    <td valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td width="120" align="right" valign="top">帐号：</td>
    <td height="35" valign="top"><?php
   $form->show('account');
	?></td>
  </tr>
  <tr>
    <td width="120" align="right" valign="top">新密码：</td>
    <td height="35" valign="top"><?php
   $form->show('new_password');
	?></td>
  </tr>
    <tr>
    <td align="right" valign="top">重复密码：</td>
    <td height="35" valign="top"><?php
   $form->show('re_new_password');
	?></td>
  </tr> 
  <tr>
    <td height="35" colspan="2"><?php $form->show('submitbtn'); ?></td>
  </tr>
</table>
<?php
	$form->end();

}
?>
</td></tr></table>