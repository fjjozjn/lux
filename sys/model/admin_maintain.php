<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
 

 
//check permission 
checkAdminPermission(PERM_MAINTAIN_ONOFF);

$isMt = $isMt ? 1 : 0;

if(!$myerror->getAny()){

	$form = new My_Forms(array('noFocus' => true));	
	// print_r_pre($tools_ip_setting);
	$formItems = array(
		'status' => array(
			'type' => 'radio', 
			'options' => array(array('正常!', '0'), array('维护中...', '1')),
			'value' => $isMt,
			),
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' 确定 '),
	);	
	

	$form->init($formItems);
	if(!$myerror->getAny() && $form->check()){
		if ($isMt != $_POST['status']){
				$mysql->sp('CALL backend_detail_withfield_noid(?, ?, ?, ?)',
							'maintained',
							'tw_sys_setting',
							'SettingField',
							'SettingID'
							);
				// echo "CALL backend_detail_withfield('".@$_GET['key']."', '".$temp_table."', 't.CouponKey', '".$list_field."');";
				$mtInfo = $mysql->fetch(1);
				
				$rtn = $mysql->sp('CALL sys_set_modify(?, ?)', $mtInfo['SettingID'], $_POST['status']);
				if($rtn > 0){				
					//add success log
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改系统维护状态为：" . ($_POST['status'] ? '维护中' : '正常'), 
							SYSTEM_MAINTAIN_SUCCESS, "", "", 0);
					$myerror->ok('开启、关闭系统维护 成功!', 'admin_maintain');						
					
				}else{
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改系统维护状态为：" . ($_POST['status'] ? '维护中' : '正常'), 
							SYSTEM_MAINTAIN_FAILURE, "", "", 0);		
					$myerror->error('开启、关闭系统维护 失败!', 'admin_maintain');							
				}
		}else{
			$myerror->error('阁下填写的设定，可能已被设定。（系统当前的维护状态未更改）');
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

	<table width="550" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">开启、关闭系统维护</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>资料</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr valign='top'>
						<td height="35">系统当前状态 : </td>  
						<td align='left'><?=($isMt ? '<span style="color:#ff0000">维护中</span>' : '<span style="color:#0000FF">正常</span>')?></td>
					</tr>	
					<tr valign='top'>
						<td height="35">开启、关闭维护 : </td>  
						<td align='left'><? $form->show('status');?></td>
					</tr>
					<tr valign='top'>
						<td colspan='2' height="35">
						<?
						$form->show('submitbtn');
						?></td>	
					</tr>	
				</table>
			</fieldset>	
			</td>	
		</tr>
	</table>
	<?
		$form->end();
	}
}else{
	require_once(ROOT_DIR.'model/inside_warn.php');
}
?>