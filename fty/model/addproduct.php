<?php

die('20170318');

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		
		'fty_id' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'fty_cost' => array('title' => '价格', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'restrict' => 'number'),
		'fty_customer_code' => array('title' => '客户编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30),
		'fty_desc' => array('title' => '产品描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'required' => 1),
        'fty_type' => array('title' => '类型', 'type' => 'select', 'options' => get_bom_lb(2)),
        'fty_sample_order_no' => array('title' => '板单编号', 'type' => 'select', 'options' => get_sample_order_no_fty()),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	
	$fty_id = $_POST['fty_id'];
    //20141207
	//$fty_sid = $_SESSION['ftylogininfo']['aName'];//从session
	$fty_sid = $_SESSION['ftylogininfo']['aFtyName'];//从session
	$fty_customer_code = $_POST['fty_customer_code'];
	$fty_date = dateMore();
	$fty_desc = $_POST['fty_desc'];
	$fty_cost = $_POST['fty_cost'];
	$fty_photo = isset($_SESSION['fty_upload_photo_add'])?$_SESSION['fty_upload_photo_add']:'';
    $fty_type = $_POST['fty_type'];
    $fty_sample_order_no = $_POST['fty_sample_order_no'];
	
	//mod by zjn 20120415 fty_id 不是唯一的，而把fty_id和fty_sid一起是唯一的，不同的厂输入的id可相同
	$judge = $mysql->q('select fty_sid from fty_product where fty_id = ?', $fty_id);
	//是否存在与当前用户fty_sid的fty_id相同的fty_id
	$sign = false;
	if($judge){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			if($v['fty_sid'] == $_SESSION['ftylogininfo']['aName']){
				$sign = true;	
			}
		}
	}
	if(!$judge || !$sign){
		$result = $mysql->q('insert into fty_product (fty_id, fty_type, fty_sample_order_no, fty_date, fty_desc, fty_sid, fty_cost, fty_photo, fty_customer_code) values ('.moreQm(9).')', $fty_id, $fty_type, $fty_sample_order_no, $fty_date, $fty_desc, $fty_sid, $fty_cost, $fty_photo, $fty_customer_code);
		if($result){
			$_SESSION['fty_upload_photo_add'] = '';

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_PRODUCT, $_SESSION["ftylogininfo"]["aName"]." <i>add product</i> '".$fty_id."' in fty", ACTION_LOG_FTY_ADD_PRODUCT_S, "", "", 0);

			$myerror->ok('新增产品资料 ('.$fty_id.') 成功!', 'searchproduct&page=1');	
		}else{
			$myerror->error('由于系统原因，新增产品资料 ('.$fty_id.') 失败', 'BACK');	
		}
	}else{
		$myerror->error('输入的产品编号已存在，新增产品资料 ('.$fty_id.') 失败', 'addproduct');	
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
<!--h1 class="green">PRODUCT<em>* indicates required fields</em></h1-->

<fieldset class="center2col" style="width:60%"> 
<legend class='legend'>1.上传图片</legend>
<?
if(isset($_SESSION['fty_upload_photo_add']) && $_SESSION['fty_upload_photo_add'] != ''){
	//非要转为GBK，不然中文 getimagesize 就认不出，太坑爹了
	$arr = getimagesize($pic_path_fty . iconv('UTF-8', 'GBK', $_SESSION['fty_upload_photo_add']));
	$pic_width = $arr[0];
	$pic_height = $arr[1];
	$image_size = getimgsize(100, 60, $pic_width, $pic_height);
	echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="'.$pic_path_fty . $_SESSION['fty_upload_photo_add'].'" class="tooltip2" target="_blank" title="'.$_SESSION['fty_upload_photo_add'].'"><img src="'.$pic_path_fty . $_SESSION['fty_upload_photo_add'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	echo "<b><a class='button' href='?act=upload_photo_add'>更换图片</a></b></div>";
}else{
	echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=upload_photo_add'>上传图片</a></div>";
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
<fieldset class="center2col" style="width:60%"> 
<legend class='legend'>2.填表</legend>
<?php
$goodsForm->begin();
?>
<table width="60%" id="table">
	<tr class="formtitle">
      	<td><? $goodsForm->show('fty_id');?></td>
        <td><? $goodsForm->show('fty_cost');?></td>
        <td><? $goodsForm->show('fty_customer_code');?></td>
	</tr>  
    <tr>
        <td colspan="2"><? $goodsForm->show('fty_desc');?></td>   
    </tr>
    <tr>
        <td><? $goodsForm->show('fty_type');?></td>
        <td><? $goodsForm->show('fty_sample_order_no');?></td>
        <td></td>
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