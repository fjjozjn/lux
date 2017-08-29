<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
 
//check permission 
//die();
//checkAdminPermission(PERM_MANAGE_ADMIN);

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}

if(!$myerror->getWarn()){

	 //get admin group 
	 /*
	$mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'fty_usergrp', '1');
	$temp_grp = $mysql->fetch(0,1);	
	for($i = 0 ; $i < count($temp_grp); $i++){
		$temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
		$row_grp[] = $temp;
	}
	*/


	$row = array();
	$gameperm_str = array();
	if (strlen(@$_GET['delid']) && isId(@$_GET['delid'])){
		$rtn = $mysql->q('delete from fty_user where FtyID = ?', $_GET['delid']);
		if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_FTY_USER, $_SESSION["logininfo"]["aName"]." <i>delete fty user</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_FTY_USER_S, "", "", 0);

			$myerror->ok('删除 Fty User 成功!', 'fty_searchuser');
		}else{
			$myerror->error('删除 Fty User 失败!', 'fty_searchuser');
		}
	}else{
		if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
			//modfiy old record, need to get admin details
			$mysql->sp('CALL backend_detail(?, ?, ?)', @$_GET['id'], 'fty_user', 'FtyID');
			$row = $mysql->fetch(1);
			//$gameperm_str = explode(',', $row['FtyGamePerm']);
			$fty_user_group = explode(',', $row['FtyGrpID']);
		}
		
	
		// print_r_pre($row);
		// die();
		// print_r_pre($gameperm_str);
		// $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));
		
		$form = new My_Forms();	
		
		$formItems = array(
		/*
			'fty_id' => array(
				'type' => 'text', 
				'value' => @$row['AdminID'],
				'readonly' => GENERAL_YES,
				),
		*/		
			'fty_login' => array(
				'type' => 'text', 
				'value' => @$row['FtyLogin'],
				'required' => GENERAL_YES,
				'minlen' => 5, 
				'maxlen' => 20,
				'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,			
				),	
			'fty_pw' => array(
				'type' => 'password', 
				'value' => '',			
				'minlen' => 6, 
				'maxlen' => 20,	
				'required' => GENERAL_YES,			
				),	
			'fty_repw' => array(
				'type' => 'password', 
				'value' => '',			
				'minlen' => 6, 
				'maxlen' => 20,	
				'required' => GENERAL_YES,		
				'compare' => 'fty_pw'	
				),								
			'fty_name' => array(
				'type' => 'select', 
				'options' => $supplier,
				'required' => GENERAL_YES,
				'nostar' => GENERAL_YES,
				'value' => @$row['FtyName'],
				),
			'fty_name_chi' => array(
				'type' => 'text', 
				'value' => @$row['FtyNameChi'],
				'required' => GENERAL_YES,
				'minlen' => 2, 
				'maxlen' => 20,
				'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
				),	
			'fty_email' => array(
				'type' => 'text', 
				'value' => @$row['FtyEmail'],
				'required' => GENERAL_YES,
				'restrict' => 'email',
				'minlen' => 0, 
				'maxlen' => 100,
				),
			'fty_user_group' => array(
				'type' => 'checkbox', 
				'checked' => @$fty_user_group,
				'options' => array(array('Partnership', '1'), array('Vendor', '2')), 
				'required' => GENERAL_YES,
				),				
				/*
			'fty_lux_group' => array(
				'type' => 'text', 
				'value' => @$row['AdminLuxGroup'],
				'required' => GENERAL_NO,
				'minlen' => 1, 
				'maxlen' => 20,			
				),
				*/	
				/*
			'fty_grp' => array(
				'type' => 'select', 			
				'value' => @$row['AdminGrpID'],
				'required' => GENERAL_YES,
				'options' => $row_grp,			
				),
			'fty_create_date' => array(
				'type' => 'text', 
				'value' => @$row['AdminCreateDate'],
				'readonly' => GENERAL_YES,
				),	
			'fty_lastlogin_ip' => array(
				'type' => 'text', 
				'value' => @$row['AdminLastLoginIP'],
				'readonly' => GENERAL_YES,
				),				
			'fty_lastlogin_date' => array(
				'type' => 'text', 
				'value' => @$row['AdminLastLoginDate'],
				'readonly' => GENERAL_YES,
				),	
			'fty_enabled' => array(
				'type' => 'radio', 
				'value' => 0 ?: @$row['AdminEnabled'],
				'options' => array(array('是', '1'), array('否', '0')), 
				'required' => GENERAL_YES, 
				),
			'fty_gameperm' => array(
				'type' => 'checkbox', 
				'options' =>$gameperm_arr,
				'checked' => $gameperm_str,	
				// 'minlen' => 0, 
				// 'maxlen' => 0, 
				'class' => 'cb_permission', 			
				),
				*/			
			'submitbtn'	=> array(
				'type' => 'submit', 'value' => ' Submit '),
		);	
		
	
		$form->init($formItems);
		// print_r_pre($formItems);
		// die();		
		if(!$myerror->getAny() && $form->check()){
		
			$gameperm_str = @implode(',',$_POST['fty_gameperm']);
			
			if (strlen(@$_GET['id'])){
				$fty_user_group = implode(',', $_POST['fty_user_group']);
				//modify or delete exist record
				// print_r_pre($_POST);
				// die();
				$result = $mysql->qone('select * from fty_user where FtyLogin = ?', $_POST['fty_login']);
				//20121029 去掉了这个判断，觉得太麻烦了
				//20121030 加这个判断是为了当数据没有改变时，不会出现修改失败的提示。原来是在下面 if($rtn > 0) 现在改成了 if($rtn !== false) ，经测试，彻底解决了这个问题了
				//if($result['FtyName'] != $_POST['fty_name'] || $result['FtyPassword'] != md5(ADMIN_PREFIX.$_POST['fty_pw']. ADMIN_POSTFIX) || $result['FtyNameChi'] != $_POST['fty_name_chi'] || $result['FtyEmail'] != $_POST['fty_email'] ){
					$rtn = $mysql->q('UPDATE fty_user SET FtyName = ?, FtyNameChi = ?, FtyPassword = ?, FtyEmail = ?, FtyGrpID = ? WHERE FtyLogin = ?', $_POST['fty_name'], $_POST['fty_name_chi'], md5(ADMIN_PREFIX.$_POST['fty_pw']. ADMIN_POSTFIX), $_POST['fty_email'], $fty_user_group, $_POST['fty_login']);
					// echo "rtn : ".$rtn;
					
					if($rtn !== false){

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_FTY_USER, $_SESSION["logininfo"]["aName"]." <i>modify fty user</i> '".$_GET['id']."'----'".$_POST['fty_name']."' in sys", ACTION_LOG_SYS_MOD_FTY_USER_S, "", "", 0);

						$myerror->ok('修改工厂用户帳號 成功!', 'fty_searchuser');
					}else{
						$myerror->warn('修改工厂用户資料 失敗!', 'fty_searchuser');	
					}
				//}else{
					//$myerror->warn('用户资料与之前一样未作改动!', 'fty_searchuser');	
				//}
			}else{
				//check admin detail
				$rtn_check = $mysql->qone('select * from fty_user where FtyLogin = ?', $_POST['fty_login']);
				if (!$rtn_check){
					$fty_user_group = implode(',', $_POST['fty_user_group']);
					//add new record			
					$rtn = $mysql->q('insert into fty_user (FtyLogin, FtyPassword, FtyName, FtyNameChi, FtyEnabled, FtyGrpID, FtyCreateDate, FtyGamePerm, FtyEmail) values (?, ?, ?, ?, ?, ?, ?, ?, ?)', $_POST['fty_login'], md5(ADMIN_PREFIX.$_POST['fty_pw']. ADMIN_POSTFIX), $_POST['fty_name'], $_POST['fty_name_chi'], 1, $fty_user_group, DateMore(), -1, $_POST['fty_email']);
					 // print_r_pre($_POST);
					 // die();
					// echo 'CALL fty_grp_insert("'.$_POST['fty_name'].'", "'.$gameperm_str.'")<BR>';
					// echo $rtn;
					if($rtn > 0){

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_ADD_FTY_USER, $_SESSION["logininfo"]["aName"]." <i>add fty user</i> '".$_POST['fty_name']."' in sys", ACTION_LOG_SYS_ADD_FTY_USER_S, "", "", 0);

						$myerror->ok('新增工厂用户帳號 成功!', 'fty_searchuser');						
					}else{
						$myerror->warn('新增工厂用户帳號 失敗!', 'fty_searchuser');							
					}
				}else{
					//account exist
					$myerror->warn('閣下填寫的用户登入名稱不可用，可能已被使用，或者含有不允許使用的字詞');
				}
			}
			
			// print_r_pre($_POST);
		}
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

	<table width="50%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center"><? if (strlen(@$_GET['id'])){echo 'Factory Account Management';}else{echo 'Create Factory Account';}?></td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>information</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<? /*
					<tr valign='top'>
						<td>NO. : </td>  
						<td align='left'><?$form->show('fty_id');?></td>
					</tr>
					*/ ?>	
					<tr align="right">
						<td width="40%">Factory Account : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_login');?></td>                     
					</tr>
					<tr align="right">
						<td>Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_pw');?></td>
					</tr>
					<tr align="right">
						<td>Confirm Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td align="left"><? $form->show('fty_repw');?></td>
					</tr>										
					<tr align="right">
						<td><h6 class="required">*</h6>Supplier : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_name');?></td>
					</tr>
                    <tr align="right">
						<td>Name : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_name_chi');?></td>
					</tr>
                    <tr align="right">
						<td>Email : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_email');?></td>
					</tr>
					<tr align="right">
						<td>Group :  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align='left'><? $form->show('fty_user_group');?></td>
					</tr>                    
                    					<? /*                    
					<tr align="right">
						<td>Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('fty_lux_group');?></td>
					</tr>
					*/ ?>						
					<? /*
					<tr valign='top'>
						<td>所屬群組 : </td>  
						<td align='left'><?$form->show('fty_grp');?></td>
					</tr>
					<tr valign='top'>
						<td>負責遊戲 : </td>  
						<td align='left'><?$form->show('fty_gameperm');?></td>
					</tr>											
					<tr valign='top'>
						<td>帳號新增日期 : </td>  
						<td align='left'><?$form->show('fty_create_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入IP : </td>  
						<td align='left'><?$form->show('fty_lastlogin_ip');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入日期 : </td>  
						<td align='left'><?$form->show('fty_lastlogin_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>可使用 : </td>  
						<td align='left'><?$form->show('fty_enabled');?></td>
					</tr>	
					*/ ?>					
					<tr>
						<td>&nbsp;</td>
						<td>
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