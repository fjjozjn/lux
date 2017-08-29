<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
judgeUserPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		$rtn = $mysql->q('delete from contact where id = ?', $_GET['delid']);
		if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_CONTACT, $_SESSION["logininfo"]["aName"]." <i>delete contact</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CONTACT_S, "", "", 0);

			$myerror->ok('删除 contact 成功!', 'com-searchcontact&page=1');
		}else{
			$myerror->error('删除 contact 失败!', 'com-searchcontact&page=1');
		}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM contact WHERE id = ?', $_GET['modid']);	
		}else{
			die('Need modid!');	
		}
			
		$goodsForm = new My_Forms();
		$formItems = array(
						
			'cid' => array('title' => 'Customer ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => get_customer(), 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
			'sid' => array('title' => 'Supplier ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => $supplier, 'value' => isset($mod_result['sid'])?$mod_result['sid']:''),
			'name' => array('title' => 'Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['name'])?$mod_result['name']:''),
			'address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
			'position' => array('title' => 'Position', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['position'])?$mod_result['position']:''),
			'fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
			'tel1' => array('title' => 'Tel 1', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['tel1'])?$mod_result['tel1']:''),
			'tel2' => array('title' => 'Tel 2', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['tel2'])?$mod_result['tel2']:''),
			'email' => array('title' => 'Email', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['email'])?$mod_result['email']:''),
			
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
			);
				
		$goodsForm->init($formItems);
		
		
		if(!$myerror->getAny() && $goodsForm->check()){
			
			$cid = $_POST['cid'];
			$sid = $_POST['sid'];
			$name = trim($_POST['name']);
			$address = $_POST['address'];
			$position = $_POST['position'];
			$fax = $_POST['fax'];
			$tel1 = $_POST['tel1'];
			$tel2 = $_POST['tel2'];
			$email = $_POST['email'];
			
			$result = $mysql->q('update contact set name = ?, address = ?, position = ?, tel1 = ?, tel2 = ?, fax = ?, email = ?, cid = ?, sid = ? where id = ?', $name, $address, $position, $tel1, $tel2, $fax, $email, $cid, $sid, $_GET['modid']);
			if($result){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_CONTACT, $_SESSION["logininfo"]["aName"]." <i>modify contact</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CONTACT_S, "", "", 0);

				$myerror->ok('修改 contact 成功!', 'com-searchcontact&page=1');	
			}else{
				$myerror->error('修改 contact 失败', 'BACK');
			}
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
	
		
		?>	
	<h1 class="green">CONTACT<em>* item must be filled in</em></h1>
	<fieldset class="center2col"> 
	<legend class='legend'>Modify Contact</legend>
	<?php
	$goodsForm->begin();
	?>
	<table width="100%">
		<tr>
			<td width="25%"><? $goodsForm->show('cid');?></td>
			<td width="25%"><? $goodsForm->show('sid');?></td>
			<td width="25%"><? $goodsForm->show('name');?></td>
			<td width="25%">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><? $goodsForm->show('address');?></td>
			<td colspan="2"><? $goodsForm->show('position');?></td>
		</tr> 
		<tr>
			<td width="25%"><? $goodsForm->show('fax');?></td>
			<td width="25%"><? $goodsForm->show('tel1');?></td>
			<td width="25%"><? $goodsForm->show('tel2');?></td>
			<td width="25%"><? $goodsForm->show('email');?></td>
		</tr>         
	</table>
	<div class="line"></div>
	<?
	$goodsForm->show('submitbtn');
	?>
	</fieldset>
	<?
	$goodsForm->end();
	
	}
}
?>
