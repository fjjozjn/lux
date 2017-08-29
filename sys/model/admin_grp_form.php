<?php
// print_r_pre($_SESSION);
 // print_r_pre($_GET);
//check permission 
checkAdminPermission(PERM_MANAGE_ADMINGRP);

if(!$myerror->getWarn()){
	$row = array();
	$perm_str = array();
	
	if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
		//modfiy old record, need to get admin group details
		$mysql->sp('CALL backend_detail(?, ?, ?)', $_GET['id'], 'tw_admingrp', 'AdminGrpID');
		$row = $mysql->fetch(1);
		$perm_str = explode(',',$row['AdminGrpDefaultPermCode']);
	}
	// print_r_pre($perm_str);
	// $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));
	
	$form = new My_Forms();	
	
	$formItems = array(
		'admingrp_id' => array(
			'type' => 'text', 
			'value' => @$row['AdminGrpID'],
			'readonly' => 1,
			),
		'admingrp_name' => array(
			'type' => 'text', 
			'value' => @$row['AdminGrpName'],
			),			
		'admingrp_perm' => array(
			'type' => 'checkbox', 
			'options' =>$permission_arr,
			'checked' => $perm_str,	
			// 'minlen' => 0, 
			// 'maxlen' => 0, 
			'class' => 'cb_permission', 			
		),
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' 確定 '),
	);	
	$form->init($formItems);
		
	if(!$myerror->getAny() && $form->check()){			
		$perm_str = @implode(',',$_POST['admingrp_perm']);
		/*
		if (array_search('-1',$_POST['admingrp_perm']) === 0){
			//have set the top permission, no need to add other permission
			$perm_str = '-1';				
		}
		*/
		if (strlen(@$_POST['admingrp_id'])){
			//modify or delete exist record
			// print_r_pre($_POST);
			// print_r_pre($perm_str);
			// echo $perm_str;
			// echo '<BR>CALL admin_grp_modify("'.$_POST['admingrp_id'].'", "'.$_POST['admingrp_name'].'", "'.$perm_str.'")<BR>';
			$rtn = $mysql->sp('CALL admin_grp_modify(?, ?, ?)',$_POST['admingrp_id'], $_POST['admingrp_name'], $perm_str);
			// echo 'CALL admin_grp_modify("'.$_POST['admingrp_id'].'", "'.$_POST['admingrp_name'].'", "'.$perm_str.'")';
			// echo "rtn : ".$rtn;
			if($rtn > 0){				
				//add success log
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改管理員群組", ADMIN_GRP_MODIFY_SUCCESS, "", "", 0);
				$myerror->ok('修改管理員群組 成功!', 'admin_grp');						
				
			}else{
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."無法修改管理員群組", ADMIN_GRP_MODIFY_FAILURE, "", "", 0);		
				$myerror->error('修改管理員群組 失敗!', 'admin_grp');							
			}				
			
		}else{
			//add new record			
			$rtn = $mysql->sp('CALL admin_grp_insert(?, ?)', $_POST['admingrp_name'], $perm_str);
			// echo 'CALL admin_grp_insert("'.$_POST['admingrp_name'].'", "'.$perm_str.'")<BR>';
			// echo $rtn;
			if($rtn > 0){				
				//add success log
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功新增管理員群組", ADMIN_GRP_ADD_SUCCESS, "", "", 0);
				$myerror->ok('新增管理員群組 成功!', 'admin_grp');						
				
			}else{
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."無法新增管理員群組", ADMIN_GRP_ADD_FAILURE, "", "", 0);		
				$myerror->error('新增管理員群組 失敗!', 'admin_grp');							
			}			
		}
		
		// print_r_pre($_POST);
	}
	// print_r_pre($_POST);
	// print_r_pre($_GET);
	// print_r_pre($GLOBALS);
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

	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">管理員群組管理</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>資料</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr valign='top'>
						<td>群組編號 : </td>  
						<td align='left'><?$form->show('admingrp_id');?></td>
					</tr>	
					<tr valign='top'>
						<td>群組名稱 : </td>  
						<td align='left'><?$form->show('admingrp_name');?></td>
					</tr>
					<tr valign='top'>
						<td>群組權限 : </td>  
						<td align='left'><?$form->show('admingrp_perm');?></td>
					</tr>							
					<tr valign='top'>
						<td colspan='2'>
						<?
						$form->show('submitbtn');
						// $form->show('resetbutton');
						$form->end();
						?></td>	
					</tr>	
				</table>
			</fieldset>	
			</td>	
		</tr>
	</table>
	<?
	}
}else{	
	require_once(ROOT_DIR.'model/inside_warn.php');
}
?>