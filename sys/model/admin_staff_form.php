<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
 
//check permission 
die();
checkAdminPermission(PERM_MANAGE_ADMIN);
if(!$myerror->getWarn()){

	 //get admin group 
	$mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
	$temp_grp = $mysql->fetch(0,1);	
	for($i = 0 ; $i < count($temp_grp); $i++){
		$temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
		$row_grp[] = $temp;
	}


	$row = array();
	$gameperm_str = array();
	
	if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
		//modfiy old record, need to get admin details
		$mysql->sp('CALL backend_detail(?, ?, ?)', @$_GET['id'], 'tw_admin', 'AdminID');
		$row = $mysql->fetch(1);
		$gameperm_str = explode(',',$row['AdminGamePerm']);
	}
	

	// print_r_pre($row);
	// die();
	// print_r_pre($gameperm_str);
	// $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));
	
	$form = new My_Forms();	
	
	$formItems = array(
		'admin_id' => array(
			'type' => 'text', 
			'value' => @$row['AdminID'],
			'readonly' => GENERAL_YES,
			),
		'admin_login' => array(
			'type' => 'text', 
			'value' => @$row['AdminLogin'],
			'required' => GENERAL_YES,
			'minlen' => 5, 
			'maxlen' => 20,
			'readonly' => @$_GET['id']?GENERAL_YES:GENERAL_NO,			
			),	
		'admin_pw' => array(
			'type' => 'password', 
			'value' => '',			
			'minlen' => 6, 
			'maxlen' => 20,	
			'required' => @$_GET['id']?GENERAL_NO:GENERAL_YES,			
			),				
		'admin_name' => array(
			'type' => 'text', 
			'value' => @$row['AdminName'],
			'required' => GENERAL_YES,
			'minlen' => 0, 
			'maxlen' => 100,			
			),	
		'admin_grp' => array(
			'type' => 'select', 			
			'value' => @$row['AdminGrpID'],
			'required' => GENERAL_YES,
			'options' => $row_grp,			
			),
		'admin_create_date' => array(
			'type' => 'text', 
			'value' => @$row['AdminCreateDate'],
			'readonly' => GENERAL_YES,
			),	
		'admin_lastlogin_ip' => array(
			'type' => 'text', 
			'value' => @$row['AdminLastLoginIP'],
			'readonly' => GENERAL_YES,
			),				
		'admin_lastlogin_date' => array(
			'type' => 'text', 
			'value' => @$row['AdminLastLoginDate'],
			'readonly' => GENERAL_YES,
			),	
		'admin_enabled' => array(
			'type' => 'radio', 
			'value' => 0 ?: @$row['AdminEnabled'],
			'options' => array(array('是', '1'), array('否', '0')), 
			'required' => GENERAL_YES, 
			),
		'admin_gameperm' => array(
			'type' => 'checkbox', 
			'options' =>$gameperm_arr,
			'checked' => $gameperm_str,	
			// 'minlen' => 0, 
			// 'maxlen' => 0, 
			'class' => 'cb_permission', 			
			),			
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' 確定 '),
	);	
	

	$form->init($formItems);
	// print_r_pre($formItems);
	// die();		
	if(!$myerror->getAny() && $form->check()){
	
		$gameperm_str = @implode(',',$_POST['admin_gameperm']);
		
		if (strlen(@$_POST['admin_id'])){
			
			//modify or delete exist record
			// print_r_pre($_POST);
			// die();
			
			$rtn = $mysql->sp('CALL admin_modify(?, ?, ?, ?, ?)',$_POST['admin_id'], $_POST['admin_name'], $_POST['admin_grp'], $gameperm_str, $_POST['admin_enabled']);
			$rtn2 = "";
			// echo 'CALL admin_modify("'.$_POST['admin_id'].'", "'.$_POST['admin_name'].'", "'.$_POST['admin_grp'].'")';
			// echo "rtn : ".$rtn;
			
			//modify password if have input
			if (strlen($_POST['admin_pw'])){
				$rtn2 = $mysql->sp('CALL admin_modify_pw(?, ?)',$_POST['admin_id'], md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX));
			}
			
			if($rtn > 0){
				//add success log
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改".$_POST['admin_name']."資料", ADMIN_MODIFY_SUCCESS, "", "", 0);
				$myerror->ok('修改管理員帳號 成功!', 'admin_staff');
			}
			if($rtn2 > 0){
				//add success log
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功修改".$_POST['admin_name']."密碼", ADMIN_MODIFY_SUCCESS, "", "", 0);
				if($rtn > 0){
					$myerror->ok('修改管理員資料 與 密碼 成功!', 'admin_staff');
				}else{
					$myerror->ok('修改管理員密碼 成功!', 'admin_staff');
				}
			}
			
			if(!$rtn && !$rtn2){
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
						, $_SESSION['logininfo']['aID'], $ip
						, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."修改".$_POST['admin_name']."資料失敗", ADMIN_MODIFY_FAILURE, "", "", 0);		
				$myerror->warn('修改管理員資料 失敗!', 'admin_staff');	
			}
			
		}else{
			//check admin detail
			$rtn_check = $mysql->sp('CALL admin_check_name(?)',$_POST['admin_login']);
			if ($rtn_check){
				//add new record			
				$rtn = $mysql->sp('CALL admin_insert(?, ?, ?, ?, ?, ?)'
								, $_POST['admin_login']
								, md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX)
								, $_POST['admin_name']
								, $_POST['admin_enabled']
								, $_POST['admin_grp']
								, $gameperm_str
								);
				 // print_r_pre($_POST);
				 // die();
				// echo 'CALL admin_grp_insert("'.$_POST['admin_name'].'", "'.$gameperm_str.'")<BR>';
				// echo $rtn;
				if($rtn > 0){				
					//add success log
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."成功新增管理員帳號 ".$_POST['admin_name'], ADMIN_ADD_SUCCESS, "", "", 0);
					$myerror->ok('新增管理員帳號 成功!', 'admin_staff');						
					
				}else{
					$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
							, $_SESSION['logininfo']['aID'], $ip
							, ADMIN_CATG_SYSTEM, $_SESSION["logininfo"]["aName"]."無法新增管理員帳號 ".$_POST['admin_name'], ADMIN_ADD_FAILURE, "", "", 0);		
					$myerror->warn('新增管理員帳號 失敗!', 'admin_staff');							
				}
			}else{
				//account exist
				$myerror->warn('閣下填寫的管理員登入名稱不可用，可能已被使用，或者含有不允許使用的字詞');
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
			<td class='headertitle' align="center">管理員帳號管理</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>資料</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr valign='top'>
						<td>管理員編號 : </td>  
						<td align='left'><?$form->show('admin_id');?></td>
					</tr>	
					<tr valign='top'>
						<td>登入名稱 : </td>  
						<td align='left'><?$form->show('admin_login');?></td>
					</tr>
					<tr valign='top'>
						<td>登入密碼 : </td>  
						<td align='left'><?$form->show('admin_pw');?></td>
					</tr>					
					<tr valign='top'>
						<td>管理員名稱 : </td>  
						<td align='left'><?$form->show('admin_name');?></td>
					</tr>	
					<tr valign='top'>
						<td>所屬群組 : </td>  
						<td align='left'><?$form->show('admin_grp');?></td>
					</tr>	
					<tr valign='top'>
						<td>負責遊戲 : </td>  
						<td align='left'><?$form->show('admin_gameperm');?></td>
					</tr>											
					<tr valign='top'>
						<td>帳號新增日期 : </td>  
						<td align='left'><?$form->show('admin_create_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入IP : </td>  
						<td align='left'><?$form->show('admin_lastlogin_ip');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入日期 : </td>  
						<td align='left'><?$form->show('admin_lastlogin_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>可使用 : </td>  
						<td align='left'><?$form->show('admin_enabled');?></td>
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