<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

/*
if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rs = $mysql->q('update material set m_stutas = 0 WHERE id = ?', $_GET['delid']);
	if($rs){
		$myerror->ok('刪除物料编号资料 成功!', 'sendform');	
	}else{
		//如果已把此物料编号添加到form中，则不能删除，数据库有删除限制
		$myerror->error('由于系统原因，刪除物料编号资料 失败', 'addmaterial');	
	}
}
*/

		
$goodsForm = new My_Forms();
$formItems = array(
		
		'pr_customer' => array('title' => 'Customer', 'type' => 'select', 'options' => array('康而能图')),
		'pr_to' => array('title' => 'To', 'type' => 'select', 'options' => array('abc')),
		'pr_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),	
		'pr_refno' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_currency' => array('title' => 'Currency', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_unit' => array('title' => 'Unit', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_packingno' => array('title' => 'Packing NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pr_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'pr_remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
		'pr_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	/*
	$m_id = $_POST['m_id'];
	$m_time = dateMore();
	$m_name = $_POST['m_name'];
	$m_color = $_POST['m_color'];
	
	$m_type_insert = '';
	foreach( $m_type as $v){
		if( $v[1] == $_POST['m_type']){
			$m_type_insert = $v[0];
			break;	
		}
	}
	$m_price = $_POST['m_price'];
	
	$m_unit_insert = '';
	foreach( $m_unit as $v){
		if( $v[1] == $_POST['m_unit']){
			$m_unit_insert = $v[0];
			break;	
		}
	}
	$m_value = '';//$_POST['m_value'];
	
	$result = $mysql->sp('CALL addmaterial(?, ?, ?, ?, ?, ?, ?, ?)', $m_id, $m_time, $m_name, $m_color, $m_type_insert, $m_price, $m_unit_insert, $m_value);
	if($result){
		$result = intval($result);
		if(is_int($result) && $result > 0){
			$myerror->ok('新增物料编号资料 成功!', 'sendform');	
		}else{
			$myerror->error('由于返回值异常，新增物料编号资料 失败', 'addmaterial');	
		}
	}else{
		$myerror->error('由于系统原因，新增物料编号资料 失败', 'addmaterial');	
	}
	*/
	$myerror->error('建设中。。。', 'index');
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
<h1 class="green">PROFORMA<em>* item must be filled in</em></h1>
<fieldset class="center2col">
<legend class='legend'>Selected products</legend>
这里显示，proforma中的products，可任意增删
</fieldset>

<fieldset class="center2col"> 
<legend class='legend'>Fill the form</legend>
<?php
$goodsForm->begin();

$goodsForm->show('pr_customer');
$goodsForm->show('pr_to');
$goodsForm->show('pr_reference');
$goodsForm->show('pr_refno');
$goodsForm->show('pr_tel');
$goodsForm->show('pr_fax');
$goodsForm->show('pr_currency');
$goodsForm->show('pr_unit');
$goodsForm->show('pr_packingno');
$goodsForm->show('pr_discount');
?>
<br />
<?
$goodsForm->show('pr_remark');
$goodsForm->show('pr_remarks', '<div class="line"></div>');

$goodsForm->show('submitbtn');
?>
</fieldset>
<?
$goodsForm->end();
?>
<fieldset class="center2col"> 
<legend class='legend'>Search proforma</legend>
这里搜索proforma，并可转为purchase或Invoice
</fieldset>
<?
}
?>