<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		
		'sid' => array('title' => 'Supplier ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'name' => array('title' => 'Company', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'required' => 1, 'addon' => 'style="width:300px"'),
		'name_en' => array('title' => 'Company(EN)', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:300px"'),
		'address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:300px"'),
		'tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'type' => array('title' => 'Type', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'category' => array('title' => 'Category', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'website' => array('title' => 'Website', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	$sid = $_POST['sid'];
	$name = trim($_POST['name']);
	$name_en = $_POST['name_en'];
	$address = $_POST['address'];
	$tel = $_POST['tel'];
	$type = $_POST['type'];
	$category = $_POST['category'];
	$website = $_POST['website'];
	$remark = $_POST['remark'];
	
	$result = $mysql->q('insert into supplier set sid = ?, name = ?, name_en = ?, address = ?, tel = ?, website = ?, remark = ?, type = ?, category = ?', $sid, $name, $name_en, $address, $tel, $website, $remark, $type, $category);
	if($result){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_ADD_SUPPLIER, $_SESSION["logininfo"]["aName"]." <i>add supplier</i> '".$sid."'----'".$name."' in sys", ACTION_LOG_SYS_ADD_SUPPLIER_S, "", "", 0);

		$myerror->ok('新增supplier 成功!', 'com-searchsupplier&page=1');	
	}else{
		$myerror->error('新增supplier 失败', 'com-addsupplier');
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
<h1 class="green">SUPPLIER<em>* item must be filled in</em></h1>
<fieldset class="center2col" style="width:70%">
<legend class='legend'>Add Supplier</legend>

<?php
$goodsForm->begin();
?>
<table width="100%">
	<tr>
    	<td width="25%"><? $goodsForm->show('sid');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
    </tr>
	<tr>
    	<td colspan="2"><? $goodsForm->show('name');?></td>
		<td colspan="2"><? $goodsForm->show('name_en');?></td>
    </tr>
    <tr valign="top">
        <td colspan="2"><? $goodsForm->show('address');?></td>
        <td><? $goodsForm->show('tel');?></td>
        <td width="25%">&nbsp;</td>
    </tr>
	<tr>
    	<td width="25%"><? $goodsForm->show('type');?></td>
        <td width="25%"><? $goodsForm->show('category');?></td>
        <td width="25%"><? $goodsForm->show('website');?></td>
        <td width="25%">&nbsp;</td>
    </tr> 
	<tr>
    	<td colspan="2"><? $goodsForm->show('remark');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
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