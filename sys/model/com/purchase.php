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
		
		'pu_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pu_supplierid' => array('title' => 'Supplier ID', 'type' => 'select', 'options' => array('康而能图')),
		'pu_to' => array('title' => 'To', 'type' => 'select', 'options' => array('abc')),	
		'pu_attention' => array('title' => 'Attention', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pu_customer' => array('title' => 'Customer', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pu_customerpo#' => array('title' => 'Customer PO#', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pu_etd' => array('title' => 'ETD', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'pu_remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
		'pu_packaging' => array('title' => 'Packaging', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
		'pu_shipmark' => array('title' => 'Ship mark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
		'pu_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
				
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
<h1 class="green">PURCHASE<em>* item must be filled in</em></h1>
<fieldset class="center2col">
<legend class='legend'>Selected products</legend>
这里显示，purchase中的products，，可任意增删
</fieldset>

<fieldset class="center2col"> 
<legend class='legend'>Fill the form</legend>
<?php
$goodsForm->begin();

$goodsForm->show('pu_reference');
$goodsForm->show('pu_supplierid');
$goodsForm->show('pu_to');
$goodsForm->show('pu_attention');
$goodsForm->show('pu_customer');
$goodsForm->show('pu_customerpo#');
$goodsForm->show('pu_etd');

?>
<br />
<?

$goodsForm->show('pu_remark');
$goodsForm->show('pu_packaging');
$goodsForm->show('pu_shipmark');
$goodsForm->show('pu_remarks', '<div class="line"></div>');

$goodsForm->show('submitbtn');

?>
</fieldset>
<?
$goodsForm->end();
?>
<fieldset class="center2col"> 
<legend class='legend'>Search purchase</legend>
这里搜索purchase
</fieldset>
<?
}
?>