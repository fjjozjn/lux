<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/
die('Please use the new page to modify product !');

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		
		'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'judgexid', 'required' => 1),
		'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => '000-000'),
		'p_theme' => array('title' => 'Theme', 'type' => 'select', 'options' => $theme),
		'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3)),
		
		'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5),
		'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5),
		
		'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => $supplier, 'required' => 1),
		'p_scode' => array('title' => 'Supplier Product code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:200px"'),
		'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer()),
        //20130715 加 select sample_order_no 改原来的 sample_order_no 为 sample_order_no_remark
        'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'select', 'options' => get_sample_order_no()),
		'p_sample_order_no_remark' => array('title' => 'Sample Order No. Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'info' => '臨時的sample order,將會刪除'),
		'p_show_in_catalog' => array('title' => 'Show in catalog', 'type' => 'checkbox', 'options' => array('show')),
		//暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
		//'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date'),
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	
	//其他的add用了js来去除头尾空格和tab，但addproduct不用js检查，因为输出js图片session保存不了的关系，所以在这里处理数据。本来是在Mysql.php中处理的，但是连换行也去掉了，textarea就不大方便了。so。。。 mod 3.5
	$pid = trim($_POST['p_pid']);
	//20130321 add theme
	$theme = $_POST['p_theme'];	
	//20130226 add product type
	$type = $_POST['p_type'];
	$in_date = dateMore();
	$cat_num = $_POST['p_cat_num'];
	$description = $_POST['p_description'];
	$description_chi = $_POST['p_description_chi'];
	$sid = $_POST['p_sid'];
	$scode= $_POST['p_scode'];
	$ccode = $_POST['p_ccode'];
	$cost_rmb = $_POST['p_cost_rmb'];
	$cost_remark = $_POST['p_cost_remark'];
	$exclusive_to = $_POST['p_exclusive_to'];
	$photos = isset($_SESSION['upload_photo_add'])?$_SESSION['upload_photo_add']:'';
    $sample_order_no = $_POST['p_sample_order_no'];
    $sample_order_no_remark = $_POST['p_sample_order_no_remark'];
	$show_in_catalog = isset($_POST['p_show_in_catalog'])?1:0;
	
	//判断是否输入的pid已存在，因为存在的话由于数据库限制，就会新增失败
	$judge = $mysql->q('select pid from product where pid = ?', $pid);
	if(!$judge){
		$result = $mysql->q('insert into product (pid, theme, type, in_date, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos, sample_order_no, sample_order_no_remark, show_in_catalog) values ('.moreQm(17).')', $pid, $theme, $type, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $sample_order_no_remark, $show_in_catalog);
		if($result){
			$myerror->ok('新增产品资料 ('.$pid.') 成功!', 'com-searchproduct&page=1');	
		}else{
			$myerror->error('由于系统原因，新增产品资料  ('.$pid.')  失败', 'BACK');	
		}
	}else{
		$myerror->error('输入的 Product ID ('.$pid.') 已存在，新增 product 失败', 'com-addproduct');	
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
<h1 class="green">PRODUCT<em>* indicates required fields</em></h1>
<fieldset class="center2col">
<legend class='legend'>Add Product</legend>
<fieldset class="center2col"> 
<legend class='legend'>1.Upload image</legend>
<?
if(isset($_SESSION['upload_photo_add']) && $_SESSION['upload_photo_add'] != ''){
	$arr = getimagesize($pic_path_com . $_SESSION['upload_photo_add']);
	$pic_width = $arr[0];
	$pic_height = $arr[1];
	//20121022 获取文件大小，在图片旁显示文件的属性
	$a = filesize($pic_path_com . $_SESSION['upload_photo_add']);	
	$image_size = getimgsize(100, 60, $pic_width, $pic_height);
	echo 'image info：('.$_SESSION['upload_photo_add'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_add'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	echo "</div><div><b><a class='button' href='?act=com-delete_photo'>DELETE</a></b></div>";
}else{
	echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=com-upload_photo_add'>UPLOAD</a></div>";
/*
<div class="line"></div>

<img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
<input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
<div class="line"></div>
<iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
*/
}
?>
</fieldset>
<fieldset class="center2col"> 
<legend class='legend'>2.Fill the form</legend>
<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
	<tr class="formtitle">
      	<td width="25%"><? $goodsForm->show('p_pid');?></td>
        <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
        <td width="25%"><? $goodsForm->show('p_type');?></td>
        <td width="25%"></td>
	</tr>
    <tr>
        <td width="25%"><? $goodsForm->show('p_sample_order_no');?></td>
        <td width="25%"><? $goodsForm->show('p_sample_order_no_remark');?></td>
        <td width="25%"></td>
        <td width="25%"></td>
    </tr>
    <tr>
      	<td colspan="2"><? $goodsForm->show('p_description');?></td>
        <td colspan="2"><? $goodsForm->show('p_description_chi');?></td>   
    </tr>
    <tr valign="top">
      	<td width="25%"><? $goodsForm->show('p_sid');?></td>
        <td width="25%"><? $goodsForm->show('p_scode');?></td>
        <td width="25%"><? $goodsForm->show('p_cost_rmb');?></td>
        <td width="25%"><? $goodsForm->show('p_cost_remark');?></td>    
    </tr>
    <tr>
      	<td width="25%"><? $goodsForm->show('p_ccode');?></td>
        <td width="25%"><? $goodsForm->show('p_exclusive_to');?></td>
        <td width="25%"><? $goodsForm->show('p_theme');?></td>
        <td width="25%"><? $goodsForm->show('p_show_in_catalog');?></td>    
    </tr>
</table>
<div class="line"></div>
<?
$goodsForm->show('submitbtn');

$goodsForm->end();
?>
</fieldset>
<?
}
?>

<script>
//$(function(){
	//有了這個photo的session沒法用，先去掉
	//judgeXid('p_pid')
//})
</script>