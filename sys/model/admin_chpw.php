<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);
// print_r_pre($perm_str); 
// if (strlen(@$_GET['id']) && isId(@$_GET['id'])){

// }
if(1){	
	$row = array();
	$perm_str = array();

	$form = new My_Forms();	
	
	$formItems = array(
		'admin_id' => array(
			'type' => 'hidden', 
			'value' => $_SESSION["logininfo"]["aID"],
			'readonly' => GENERAL_YES,
			),		
		'admin_pw' => array(			
			'type' => 'password', 
			'value' => '',			
			'minlen' => 6, 
			'maxlen' => 20,	
			'required' => GENERAL_YES,			
			),	
		'admin_new_pw' => array(
			
			'type' => 'password', 
			'value' => '',			
			'minlen' => 6, 
			'maxlen' => 20,				
			'required' => GENERAL_YES,				
			),	
		'admin_new_pw_confirm' => array(
			
			'type' => 'password', 
			'value' => '',			
			'minlen' => 6, 
			'maxlen' => 20,	
			'required' => GENERAL_YES,				
			'compare' => 'admin_new_pw',
			),				
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' 确定 '),
	);	
	

	$form->init($formItems);
	// print_r_pre($formItems);
	// die();		
	if(!$myerror->getAny() && $form->check()){			
		if (strlen(@$_POST['admin_id'])){
			//check old pw must be correct
			$rtn = $mysql->sp('CALL admin_check_password(?, ?)',$_POST['admin_id'],md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX));			
			
			if ($rtn == 1){
				//modify password
				$result = $mysql->sp('CALL admin_modify_pw(?, ?)',$_POST['admin_id'], md5(ADMIN_PREFIX.$_POST['admin_new_pw']. ADMIN_POSTFIX));
				// echo 'CALL admin_modify_pw("'.$_POST['admin_id'].'", "'.md5(ADMIN_PREFIX.$_POST['admin_new_pw']. ADMIN_POSTFIX).'")';
				if($result == 1){				
					//add success log
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改密码", PASSWORD_MODIFY_SUCCESS, "", "", 0);
					$myerror->ok('修改密码 成功!', 'admin_chpw');						
					
				}else{
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."无法修改密码", PASSWORD_MODIFY_FAILURE, "", "", 0);		
					$myerror->error('修改密码 失败!', 'admin_chpw');							
				}					
			}else{
				//password incorrect
				$myerror->error('旧密码不正确!');
			}			
			
		}else{
			$myerror->error('操作失败，请返回');
		}
	}
if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}else{
	if($myerror->getWarn()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	$form->begin();	
	?>
	<table width="450" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">修改个人密码</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset class="center2col">
			<legend class='legend'>资料</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr valign='top'>
						<td width='40%'>旧密码 : </td>  
						<td width='60%' align='left'><? $form->show('admin_pw');?></td>			
					</tr>	
					<tr valign='top'>
						<td width='40%'>新密码 : </td>  
						<td width='60%' align='left'><? $form->show('admin_new_pw');?></td>			
					</tr>	
					<tr valign='top'>
						<td width='40%'>确认密码 : </td>  
						<td width='60%' align='left'><? $form->show('admin_new_pw_confirm');?></td>			
					</tr>						
				</table>			
						<?						
						$form->show('admin_id');					
						$form->show('submitbtn');	
						$form->end();
						?>

			</fieldset>	
			</td>	
		</tr>
	</table>
	<?
	}
}else{
	$myerror->error('阁下没有进入本页权限，请返回');
	require_once(ROOT_DIR.'model/inside_warn.php');
}
?>