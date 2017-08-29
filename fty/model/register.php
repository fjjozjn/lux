<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//checkAdminAllowedIP();

$form = new My_Forms();
$formItems = array(
		'account' => array('type' => 'text', 'minlen' => 5, 'maxlen' => 15, 'required' => 1, 'restrict' => 'account'),
		'password' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15, 'required' => 1),
		'repassword' => array('type' => 'password', 'minlen' => 6, 'maxlen' => 15,'required' => 1, 'compare' => 'password'),
		'fty_name' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1),
		'name' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'required' => 1),
		'job_title' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'address' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),
		'tel' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'fax' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'email' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 40, 'required' => 1, 'restrict' => 'email'),
		'qq' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 40, 'required' => 1),
		'sex' => array('type' => 'radio', 'required' => 1, 'options' => array(array('先生', '1'), array('女士', '2'))),
		'other' => array('type' => 'textarea', 'minlen' => 0, 'maxlen' => 200),	
		
		'submitbtn' => array('type' => 'submit', 'value' => ' 提交 '),
		);
$form->init($formItems);

if(!$myerror->getAny() && $form->check()){
	$account = $_POST['account'];
	$password = $_POST['password'];
	$fty_name = $_POST['fty_name'];
	$name = $_POST['name'];
	$job_title = $_POST['job_title'];
	$address = $_POST['address'];
	$tel = $_POST['tel'];
	$fax = $_POST['fax'];
	$email = $_POST['email'];
	$qq = $_POST['qq'];			
	$sex = $_POST['sex'];
	$other = $_POST['other'];	
		
	//检测用户名是否已存在
	$rs = $mysql->qone('select AdminID from tw_admin where AdminLogin = ?', $account);
	if($rs){
		//add failure log
		$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
				, 0, $ip_real
				, FTY_CATG_REG, $_POST['account']." 帐号已经存在", FTY_ACTION_REG_FAILURE, "", "", 0);		
		$myerror->warn('您填写的帐号已经存在，请换一个帐号。');
	}else{
		//send email to admin
		require_once(ROOT_DIR.'class/Mail/mail.php');
		$account_info = array('date' => date('Y-m-d'));
		//提交的信息
		$info = '帐号：'.$account.'， 密码：'.$password.'， 工厂名：'.$fty_name.'， 姓名：'.$name.'， 职位：'.$job_title.'， 地址：'.$address.'， 电话：'.$tel.'， Fax：'.$fax.'， 电邮：'.$email.'， QQ：'.$qq.'， 性别：'.$sex.'， 其他：'.$other;

        $rtn_setting = $mysql->qone('select email_fty_user_info_to from setting');

		send_mail($rtn_setting['email_fty_user_info_to'], '', 'http://'.$host.'/fty 工厂用户注册信息', $info,
            $account_info);
		
		//add success log
		$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
				, 0, $ip_real
				, FTY_CATG_REG, '工厂注册信息： '.$info, FTY_ACTION_REG_SUCCESS, "", "", 0);
		$myerror->ok('您输入的帐号信息已提交，我们审批过后会马上通知您。', 'CLOSE');
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
	<td colspan="2" align="center"><font color="#FF0000">请您填写信息并提交，我们审批后，会激活帐号并通知您。<br />如果您已经有帐号，请点<a href="?act=login">这里登入</a>。</font></td>
  </tr>
  <tr><td>&nbsp;</td></tr>
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
	<td align="right" valign="top">确认密码：</td>
	<td height="35" valign="top"><?php
	$form->show('repassword');
	?></td>
  </tr>  
  <tr>
	<td align="right" valign="top">工厂名：</td>
	<td height="35" valign="top"><?php
	$form->show('fty_name');
	?></td>
  </tr> 
  <tr>
	<td align="right" valign="top">您的姓名：</td>
	<td height="35" valign="top"><?php
	$form->show('name');
	?></td>
  </tr> 
  <tr>
	<td align="right" valign="top">职位：</td>
	<td height="35" valign="top"><?php
	$form->show('job_title');
	?></td>
  </tr> 
  <tr>
	<td align="right" valign="top">地址：</td>
	<td height="35" valign="top"><?php
	$form->show('address');
	?></td>
  </tr> 
  <tr>
	<td align="right" valign="top">电话：</td>
	<td height="35" valign="top"><?php
	$form->show('tel');
	?></td>
  </tr>
  <tr>
	<td align="right" valign="top">Fax：</td>
	<td height="35" valign="top"><?php
	$form->show('fax');
	?></td>
  </tr>  
  <tr>
	<td align="right" valign="top">电邮：</td>
	<td height="35" valign="top"><?php
	$form->show('email');
	?></td>
  </tr>  
  <tr>
	<td align="right" valign="top">QQ号码：</td>
	<td height="35" valign="top"><?php
	$form->show('qq');
	?></td>
  </tr>
  <tr>
	<td align="right" valign="top">性别：</td>
	<td height="35" valign="top"><?php
	$form->show('sex');
	?></td>
  </tr> 
  <tr>
	<td align="right" valign="top">其他：</td>
	<td height="35" valign="top"><?php
	$form->show('other');
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
