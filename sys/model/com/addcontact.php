<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		
		'cid' => array('title' => 'Customer ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => get_customer()),
		'sid' => array('title' => 'Supplier ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => $supplier),
		'name' => array('title' => 'Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200),
		'position' => array('title' => 'Position', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50),
		'fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'tel1' => array('title' => 'Tel 1', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'tel2' => array('title' => 'Tel 2', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'email' => array('title' => 'Email', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	$cid = isset($_POST['cid'])?$_POST['cid']:'';
	$sid = isset($_POST['sid'])?$_POST['sid']:'';
	$name = trim($_POST['name']);
	$address = $_POST['address'];
	$position = $_POST['position'];
	$fax = $_POST['fax'];
	$tel1 = $_POST['tel1'];
	$tel2 = $_POST['tel2'];
	$email = $_POST['email'];
	
	$result = $mysql->q('insert into contact (name, address, position, tel1, tel2, fax, email, cid, sid) values ('.moreQm(9).')', $name, $address, $position, $tel1, $tel2, $fax, $email, $cid, $sid);
	if($result){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_ADD_CONTACT, $_SESSION["logininfo"]["aName"]." <i>add contact</i> '".$sid."'----'".$name."' to '".$cid.$sid."' in sys", ACTION_LOG_SYS_ADD_CONTACT_S, "", "", 0);

		$myerror->ok('新增contact 成功!', 'com-searchcontact&page=1');	
	}else{
		$myerror->error('新增contact 失败', 'com-addcontact');
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
<legend class='legend'>Add Contact</legend>

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
?>