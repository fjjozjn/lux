<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rtn = $mysql->q('delete from supplier where sid = ?', $_GET['delid']);
	if($rtn){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_DEL_SUPPLIER, $_SESSION["logininfo"]["aName"]." <i>delete supplier</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_SUPPLIER_S, "", "", 0);

		$myerror->ok('删除 supplier 成功!', 'com-searchsupplier&page=1');
	}else{
		$myerror->error('删除 supplier 失败!', 'com-searchsupplier&page=1');
	}
}else{
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM supplier WHERE sid = ?', $_GET['modid']);	
	}else{
		die('Need modid!');	
	}
		
	$goodsForm = new My_Forms();
	$formItems = array(
			
		'sid' => array('title' => 'Supplier ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['sid'])?$mod_result['sid']:'', 'readonly' => 'readonly'),
		'name' => array('title' => 'Company', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'required' => 1, 'addon' => 'style="width:300px"', 'value' => isset($mod_result['name'])?$mod_result['name']:''),
		'name_en' => array('title' => 'Company(EN)', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:300px"', 'value' => isset($mod_result['name_en'])?$mod_result['name_en']:''),
        'address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:300px"', 'value' => isset($mod_result['address'])?$mod_result['address']:''),
        'tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
		'type' => array('title' => 'Type', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['type'])?$mod_result['type']:''),
		'category' => array('title' => 'Category', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['category'])?$mod_result['category']:''),
		'website' => array('title' => 'Website', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['website'])?$mod_result['website']:''),
		'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
			
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
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
		
		$result = $mysql->q('update supplier set name = ?, name_en = ?, address = ?, tel = ?, type = ?, category = ?, website = ?, remark = ? where sid = ?', $name,  $name_en, $address, $tel, $type, $category, $website, $remark, $_GET['modid']);
		if($result){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_MOD_SUPPLIER, $_SESSION["logininfo"]["aName"]." <i>modify supplier</i> ID:'".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_SUPPLIER_S, "", "", 0);

			$myerror->ok('修改 supplier 成功!', 'com-searchsupplier&page=1');	
		}else{
			$myerror->error('修改 supplier 失败', 'BACK');
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
<h1 class="green">SUPPLIER<em>* item must be filled in</em></h1>

<fieldset class="center2col" style="width:70%"> 
<legend class='legend'>Action</legend>
<div style="margin-left:28px;"><a class="button" href="?act=com-addcontact&sid=<?=$mod_result['sid']?>" onclick="return pdfConfirm()">Add Contact</a></div>
</fieldset>

<fieldset class="center2col" style="width:70%"> 
<legend class='legend'>Modify Supplier</legend>
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
