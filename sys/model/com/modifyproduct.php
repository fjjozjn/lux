<?php

/*
change log

*/

die('Please use the new page to modify product !');

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rtn = $mysql->q('delete from product where pid = ?', $_GET['delid']);
	if($rtn){
		$myerror->ok('删除产品资料 ('.$_GET['delid'].') 成功!', 'com-searchproduct&page=1');
	}else{
		$myerror->error('删除产品资料 ('.$_GET['delid'].') 失败', 'com-searchproduct&page=1');	
	}
}else{
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$_SESSION['modid'] = $_GET['modid'];
		$mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['modid']);	
		if( !isset($_SESSION['upload_photo_mod']) || $_SESSION['upload_photo_mod'] == ''){
			$_SESSION['upload_photo_mod'] = $mod_result['photos'];
		}
	}elseif(isset($_GET['copypid']) && $_GET['copypid'] != ''){
		$_SESSION['modid'] = $_GET['copypid'];
		$mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['copypid']);	
		if( !isset($_SESSION['upload_photo_mod']) || $_SESSION['upload_photo_mod'] == ''){
			$_SESSION['upload_photo_mod'] = $mod_result['photos'];
		}	
		$_SESSION['copyid'] = $_GET['copypid'];
		$mod_result['pid'] = '';
	}
	
	
			
	$goodsForm = new My_Forms();
	$formItems = array(
			
			'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, (isset($_GET['copypid']) || isset($_GET['copy']))?'':'readonly' => 'readonly', 'value' => isset($mod_result['pid'])?$mod_result['pid']:''),
			'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cat_num'])?$mod_result['cat_num']:'000-000'),
			'p_theme' => array('title' => 'Theme', 'type' => 'select', 'options' => $theme, 'value' => isset($mod_result['theme'])?$mod_result['theme']:''),
			'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3), 'value' => isset($mod_result['type'])?$mod_result['type']:''),
			
			'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['description'])?$mod_result['description']:''),
			'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['description_chi'])?$mod_result['description_chi']:''),
			
			'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => $supplier, 'value' => isset($mod_result['sid'])?$mod_result['sid']:'', 'required' => 1),
			'p_scode' => array('title' => 'Supplier Product code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['scode'])?$mod_result['scode']:'', 'required' => 1),
			'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['ccode'])?$mod_result['ccode']:''),
			'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'required' => 1),
			'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:'', 'addon' => 'style="width:200px"'),
			'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['exclusive_to'])?$mod_result['exclusive_to']:''),
			//暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
			//現在定為可修改，新增product就指定當天的
            //20130619 去掉自己改时间，in_date 字段为新增时间，或者修改时间
			//'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['in_date'])?$mod_result['in_date']:''),
            //20130715 加 select sample_order_no 改原来的 sample_order_no 为 sample_order_no_remark
            'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'select', 'options' => get_sample_order_no(), 'value' => isset($mod_result['sample_order_no'])?$mod_result['sample_order_no']:''),
            'p_sample_order_no_remark' => array('title' => 'Sample Order No. Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['sample_order_no_remark'])?$mod_result['sample_order_no_remark']:'', 'info' => '臨時的sample order,將會刪除'),
			'p_show_in_catalog' => array('title' => 'Show in catalog', 'type' => 'checkbox', 'options' => array('show'), 'value' => (isset($mod_result['show_in_catalog']) && $mod_result['show_in_catalog'] == 1)?'show':''),
					
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);
	$goodsForm->init($formItems);
	
	
	if(!$myerror->getAny() && $goodsForm->check()){
	
		$pid = $_POST['p_pid'];
		//20130321 add theme
		$theme = $_POST['p_theme'];		
		//20130226 add product type
		$type = $_POST['p_type'];
        //20130619 cindy 让我改为显示修改的时间
		$in_date = dateMore();//$_POST['p_in_date'];
		$cat_num = $_POST['p_cat_num'];
		$description = $_POST['p_description'];
		$description_chi = $_POST['p_description_chi'];
		$sid = $_POST['p_sid'];
		$scode= $_POST['p_scode'];
		$ccode = $_POST['p_ccode'];
		$cost_rmb = $_POST['p_cost_rmb'];
		$cost_remark = $_POST['p_cost_remark'];
		$exclusive_to = $_POST['p_exclusive_to'];
		$photos = isset($_SESSION['upload_photo_mod'])?$_SESSION['upload_photo_mod']:'';
		$sample_order_no = $_POST['p_sample_order_no'];
        $sample_order_no_remark = $_POST['p_sample_order_no_remark'];
        $show_in_catalog = isset($_POST['p_show_in_catalog'])?1:0;
		
		if(isset($_GET['copypid'])){
            //判断是否输入的pid已存在，因为存在的话由于数据库限制，就会新增失败
            $judge = $mysql->q('select pid from product where pid = ?', $pid);
            if(!$judge){
                $result = $mysql->q('insert into product (pid, theme, type, in_date, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos, sample_order_no, sample_order_no_remark, show_in_catalog) values ('.moreQm(17).')', $pid, $theme, $type, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $sample_order_no_remark, $show_in_catalog);
                //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                if($result !== false){
                    $myerror->ok('新增产品资料 ('.$pid.') 成功!', 'com-searchproduct&page=1');
                }else{
                    $myerror->error('由于系统原因，新增产品资料 ('.$pid.') 失败', 'BACK');
                }
            }else{
                $myerror->error('输入的 Product ID ('.$pid.') 已存在，copy product 失败', 'BACK');
            }
		}else{
			$result = $mysql->q('update product set pid = ?, theme = ?, type = ?, in_date = ?, cat_num = ?, description = ?, description_chi = ?, sid = ?, scode = ?, ccode = ?, cost_rmb = ?, cost_remark = ?, exclusive_to = ?, photos = ?, sample_order_no = ?, sample_order_no_remark = ?, show_in_catalog = ? where pid = ?', $pid, $theme, $type, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $sample_order_no_remark, $show_in_catalog, $mod_result['pid']);
			//這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
			if($result !== false){
				$myerror->ok('修改产品资料 ('.$pid.') 成功!', 'com-searchproduct&page=1');	
			}else{
				$myerror->error('由于系统原因，修改产品资料 ('.$pid.') 失败', 'BACK');	
			}
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
<h1 class="green">PRODUCT<em>* indicates required fields</em></h1>
<fieldset class="center2col"> 
<legend class='legend'>Action</legend>
<div style="margin-left:28px;"><a class="button" href="?act=com-modifyproduct&copypid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a id="<?=$_GET['modid']?>" class="button" href="#" onclick="bomConfirm(this)"><b>Bom</b></a><a class="button" href="#" onclick="window.open ('model/com/proforma_pid_history.php?pid='+$('#p_pid').val(),'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no')"><b>History(PI)</b></a></div>
</fieldset>
<fieldset class="center2col">
<legend class='legend'><? if(isset($_GET['copypid'])){ echo 'Copy Product';}else{ echo 'Modify Product';}?></legend>
<fieldset class="center2col"> 
<legend class='legend'>1.Upload image</legend>
<?
if(isset($_SESSION['upload_photo_mod']) && $_SESSION['upload_photo_mod'] != ''){
	if (is_file($pic_path_com . $_SESSION['upload_photo_mod']) == true) { 
		$arr = getimagesize($pic_path_com . $_SESSION['upload_photo_mod']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		//20121022 获取文件大小，在图片旁显示文件的属性
		$a = filesize($pic_path_com . $_SESSION['upload_photo_mod']);
		$image_size = getimgsize(100, 60, $pic_width, $pic_height);
		echo 'image info：('.$_SESSION['upload_photo_mod'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_mod'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	}else{
		echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
	}
	if(isset($_GET['modid'])){//copy的时候，删了图片，就连原来的product的图片也空了，所以这里去掉了
		echo "</div><div><b><a class='button' href='?act=com-delete_photo&pid=".$mod_result['pid']."'>DELETE</a></b></div>";
	}
}else{
	echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=com-upload_photo_mod'>UPLOAD</a></div>";

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
    <tr valign="top">
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
    <tr>
      	<td width="25%"><? //$goodsForm->show('p_in_date');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>    
    </tr>
</table>
<div class="line"></div>

<?
$goodsForm->show('submitbtn');
?>
</fieldset>
</fieldset>
<fieldset class="center2col"> 
<legend class='legend'>Action</legend>
<div style="margin-left:28px;"><a class="button" href="?act=com-modifyproduct&copypid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a id="<?=$_GET['modid']?>" class="button" href="#" onclick="bomConfirm(this)"><b>Bom</b></a><a class="button" href="#" onclick="window.open ('model/com/proforma_pid_history.php?pid='+$('#p_pid').val(),'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no')"><b>History(PI)</b></a></div>
</fieldset>
<?
$goodsForm->end();

}
?>