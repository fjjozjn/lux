<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['modid']);
	$rtn_s = $mysql->qone('select markup from setting limit 1');
	$rtn_c = $mysql->qone('select rate from currency where type = ?', 'USD');
	$price = formatMoney($mod_result['cost_rmb'] * $rtn_s['markup'] * $rtn_c['rate']);
	if( !isset($_SESSION['upload_photo_mod']) || $_SESSION['upload_photo_mod'] == ''){
		$_SESSION['upload_photo_mod'] = $mod_result['photos'];
	}
}
	
$goodsForm = new My_Forms();
$formItems = array(
		
		'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['pid'])?$mod_result['pid']:''),
		'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'readonly' => 'readonly', 'value' => isset($mod_result['cat_num'])?$mod_result['cat_num']:'000-000'),
		
		'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'readonly' => 'readonly', 'value' => isset($mod_result['description'])?$mod_result['description']:''),
		'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'readonly' => 'readonly', 'value' => isset($mod_result['description_chi'])?$mod_result['description_chi']:''),
		
		'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'readonly' => 'readonly', 'value' => isset($mod_result['ccode'])?$mod_result['ccode']:''),
		'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['exclusive_to'])?$mod_result['exclusive_to']:''),
		//暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
		//現在定為可修改，新增product就指定當天的
		'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['in_date'])?$mod_result['in_date']:''),
		
		'p_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'value' => 'USD'),
		'p_price' => array('title' => 'Price(USD)', 'type' => 'text', 'readonly' => 'readonly', 'value' => $price != ''?$price:''),		
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	//没有提交按钮，所以提交之后的处理也就不需要了
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
<h1 class="green">PRODUCT<em>* indicates required fields</em></h1>
<fieldset class="center2col"> 
<legend class='legend'>Product Photo</legend>
<?
if(isset($_SESSION['upload_photo_mod']) && $_SESSION['upload_photo_mod'] != ''){
	if (is_file($pic_path_com . $_SESSION['upload_photo_mod']) == true) { 
		$arr = getimagesize($pic_path_com . $_SESSION['upload_photo_mod']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(500, 300, $pic_width, $pic_height);
		echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_mod'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	}else{
		echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
	}
}else{
	echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'></div>";
}
?>
</fieldset>
<fieldset class="center2col"> 
<legend class='legend'>Information</legend>
<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
	<tr class="formtitle">
      	<td width="25%"><? $goodsForm->show('p_pid');?></td>
        <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
	</tr>
    <tr>
      	<td colspan="2"><? $goodsForm->show('p_description');?></td>
        <td colspan="2"><? $goodsForm->show('p_description_chi');?></td>   
    </tr>
    <tr>
      	<td width="25%"><? $goodsForm->show('p_ccode');?></td>
        <td width="25%"><? $goodsForm->show('p_exclusive_to');?></td>
        <td width="25%"><? $goodsForm->show('p_in_date');?></td>
        <td width="25%">&nbsp;</td>    
    </tr>
    <tr>
      	<td width="25%"><? $goodsForm->show('p_currency');?></td>
        <td width="25%"><? $goodsForm->show('p_price');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>    
    </tr>    
</table>
<div class="line"></div>

</fieldset>


<?
$goodsForm->end();

}
?>

<script>
$(function(){
	p_currency();
	$("input").removeClass('readonly');
})
</script>