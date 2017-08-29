<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
 
//check permission 
//die();
//checkAdminPermission(PERM_MANAGE_ADMIN);

if(!$myerror->getWarn()){

	$row = array();
	$gameperm_str = array();
	if (strlen(@$_GET['delid']) && isId(@$_GET['delid'])){
		$rtn = $mysql->q('delete from fty_client where id = ?', $_GET['delid']);
		if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_DEL_CLIENT, $_SESSION["ftylogininfo"]["aName"]." <i>delete client</i> ID:'".$_GET['delid']."' in fty", ACTION_LOG_FTY_DEL_CLIENT_S, "", "", 0);

			$myerror->ok('删除 客户资料 成功!', 'searchclient');
		}else{
			$myerror->error('删除 客户资料 失败!', 'searchclient');
		}
	}else{
		if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
			//modfiy old record, need to get admin details
			$mysql->sp('CALL backend_detail(?, ?, ?)', @$_GET['id'], 'fty_client', 'id');
			$row = $mysql->fetch(1);
		}
		
	
		// print_r_pre($row);
		// die();
		// print_r_pre($gameperm_str);
		// $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));
		
		$form = new My_Forms();	
		
		$formItems = array(	
			'company' => array(
				'type' => 'text', 
				'value' => @$row['company'],
				'required' => GENERAL_YES,
				'minlen' => 1, 
				'maxlen' => 50,
				),								
			'contact' => array(
				'type' => 'text', 
				'value' => @$row['contact'],
				'required' => GENERAL_NO,
				'minlen' => 1, 
				'maxlen' => 30,
				),
			'tel' => array(
				'type' => 'text', 
				'value' => @$row['tel'],
				'required' => GENERAL_NO,
				'minlen' => 1, 
				'maxlen' => 20,
				),	
			'address' => array(
				'type' => 'text', 
				'value' => @$row['address'],
				'required' => GENERAL_YES,
				'minlen' => 1, 
				'maxlen' => 100,
				),
			'submitbtn'	=> array(
				'type' => 'submit', 'value' => ' Submit '),
		);	
		
	
		$form->init($formItems);
		// print_r_pre($formItems);
		// die();		
		if(!$myerror->getAny() && $form->check()){
					
			if (strlen(@$_GET['id'])){
				//20130228 加了mod_date字段，这样就不存在资料全部没变化，导致数据提交后，$rs为false的情况了
				$rs = $mysql->q('update fty_client set company = ?, contact = ?, tel = ?, address = ?, mod_date = ? where id = ?', $_POST['company'], $_POST['contact'], $_POST['tel'], $_POST['address'], dateMore(), $_GET['id']);
				if($rs){

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['ftylogininfo']['aID'], $ip_real
                        , ACTION_LOG_FTY_MOD_CLIENT, $_SESSION["ftylogininfo"]["aName"]." <i>modify client</i> ID:'".$_GET['id']."' in fty", ACTION_LOG_FTY_MOD_CLIENT_S, "", "", 0);

					$myerror->ok('修改 客户资料 成功!', 'searchclient&page=1');
				}else{
					$myerror->error('修改 客户资料 失败', 'searchclient&page=1');	
				}
			}else{
				$rs = $mysql->q('insert into fty_client values (NULL, '.moreQm(7).')', $_POST['company'], $_POST['contact'], $_POST['tel'], $_POST['address'], dateMore(), '', $_SESSION['ftylogininfo']['aName']);
				if($rs){

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['ftylogininfo']['aID'], $ip_real
                        , ACTION_LOG_FTY_ADD_CLIENT, $_SESSION["ftylogininfo"]["aName"]." <i>add client</i> COMPANY:'".$_POST['company']."' in fty", ACTION_LOG_FTY_ADD_CLIENT_S, "", "", 0);

					$myerror->ok('创建 客户资料 成功!', 'searchclient&page=1');	
				}else{
					$myerror->error('创建 客户资料 失败', 'searchclient&page=1');	
				}
			}			
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

	<table width="40%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center"><? if (strlen(@$_GET['id'])){echo '修改客户资料';}else{echo '创建客户资料';}?></td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>信息</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr align="right">
						<td width="35%">公司 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td width="65%" align="left"><? $form->show('company');?></td>                     
					</tr>
					<tr align="right">
						<td>联络人 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('contact');?></td>
					</tr>
					<tr align="right">
						<td>电话 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td align="left"><? $form->show('tel');?></td>
					</tr>										
					<tr align="right">
						<td>地址 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('address');?></td>
					</tr>							
					<tr>
						<td>&nbsp;</td>
						<td>
						<?
						$form->show('submitbtn');
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