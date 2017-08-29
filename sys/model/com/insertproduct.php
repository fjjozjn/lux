<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(!isset($_GET['inid']) || !isset($_GET['insid'])){

	$myerror->warn('缺少参数！', 'fty_searchproduct&page=1');	
	
}else{
	$rtn = $mysql->qone('select * from fty_product where fty_sid = ? and fty_id = ?', $_GET['insid'], $_GET['inid']);
    $rtn_so = array();
    if(isset($rtn['fty_sample_order_no']) && $rtn['fty_sample_order_no'] != ''){
        $rtn_so = $mysql->qone('select customer from sample_order where so_no = ?', $rtn['fty_sample_order_no']);
    }
    $goodsForm = new My_Forms();
	$formItems = array(
			
			'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
			'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => '000-000'),
            'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3), 'value' => isset($rtn['fty_type'])?$rtn['fty_type']:''),
            'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'select', 'options' => get_sample_order_no(), 'value' => isset($rtn['fty_sample_order_no'])?$rtn['fty_sample_order_no']:''),

            'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'addon' => 'style="width:400px"'),
			'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'value' => isset($rtn['fty_desc'])?$rtn['fty_desc']:'', 'addon' => 'style="width:400px"'),
			
			'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => $supplier, 'value' => isset($rtn['fty_sid'])?$rtn['fty_sid']:'', 'required' => 1),
			'p_scode' => array('title' => 'Supplier code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($rtn['fty_id'])?$rtn['fty_id']:'', 'required' => 1),
			'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($rtn['fty_customer_code'])?$rtn['fty_customer_code']:''),
			'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($rtn['fty_cost'])?$rtn['fty_cost']:'', 'required' => 1),
			'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
			'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer(), 'value' => isset($rtn_so['customer'])?$rtn_so['customer']:''),
			//暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
			//現在定為可修改，新增product就指定當天的
			//'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['in_date'])?$mod_result['in_date']:''),
					
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);
	$goodsForm->init($formItems);
	
	
	if(!$myerror->getAny() && $goodsForm->check()){
	
		$pid = $_POST['p_pid'];
		$in_date = /*$_POST['p_in_date'];*/dateMore();
		$cat_num = $_POST['p_cat_num'];
        //20130712 加 type 和 sample_order_no
        $type = $_POST['p_type'];
        $sample_order_no = $_POST['p_sample_order_no'];
        $description = $_POST['p_description'];
		$description_chi = $_POST['p_description_chi'];
		$sid = $_POST['p_sid'];
		$scode= $_POST['p_scode'];
		$ccode = $_POST['p_ccode'];
		$cost_rmb = $_POST['p_cost_rmb'];
		$cost_remark = $_POST['p_cost_remark'];
		$exclusive_to = $_POST['p_exclusive_to'];
		$photos = isset($rtn['fty_photo'])?$rtn['fty_photo']:'';
		if($photos != ''){
			$temp = explode('.', $photos);	
		}

        //20130830 加判断pid是否重复
        $judge = $mysql->q('select pid from product where pid = ?', $pid);
        if(!$judge){
            $result = $mysql->q('insert into product (pid, in_date, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos, type, sample_order_no) values ('.moreQm(14).')', $pid, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $pid.'.'.@$temp[1], $type, $sample_order_no);
            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){
                //insert product 成功后才copy图片到sys的目录下
                if($rtn['fty_photo'] != '' && file_exists(iconv('UTF-8','GBK', '../fty/upload/fty/'.$rtn['fty_sid'].'/'.$rtn['fty_photo']))){
                    copy(iconv('UTF-8','GBK', '../fty/upload/fty/'.$rtn['fty_sid'].'/'.$rtn['fty_photo']), iconv('UTF-8','GBK', 'upload/lux/'.$pid.'.'.@$temp[1]));
                }
                $mysql->q('update fty_product set fty_isin = 1 where fty_id = ?', $rtn['fty_id']);
                $myerror->ok('新增产品资料 成功!', 'com-fty_searchproduct&page='.$_GET['page']);
            }else{
                $myerror->error('由于系统原因，新增产品资料 失败', 'BACK');
            }
        }else{
            $myerror->error('Product ID ('.$pid.') already exist, add product failure!', 'BACK');
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

<fieldset>
<legend class='legend'>Insert Product</legend>
<fieldset> 
<legend class='legend'>1.Upload image</legend>
<?
	if (is_file('../fty/upload/fty/' . iconv('UTF-8', 'GBK', $rtn['fty_sid'] . '/' . $rtn['fty_photo'])) == true) { 
		$arr = getimagesize('../fty/upload/fty/' . iconv('UTF-8', 'GBK', $rtn['fty_sid'] . '/' . $rtn['fty_photo']));
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(100, 60, $pic_width, $pic_height);
		echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/upload/fty/' . $rtn['fty_sid'] .'/'. $rtn['fty_photo'].'" class="tooltip2" target="_blank" title="'.$rtn['fty_photo'].'"><img src="/fty/upload/fty/' . $rtn['fty_sid'] .'/'. $rtn['fty_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	}else{
		echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
	}


/*
<div class="line"></div>

<img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
<input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
<div class="line"></div>
<iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
*/

?>
</fieldset>
<fieldset> 
<legend class='legend'>2.Fill the form</legend>
<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
	<tr class="formtitle">
      	<td width="25%"><? $goodsForm->show('p_pid');?></td>
        <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
        <td width="25%"><? $goodsForm->show('p_type');?></td>
        <td width="25%"><? $goodsForm->show('p_sample_order_no');?></td>
	</tr>
    <tr>
      	<td colspan="2"><? $goodsForm->show('p_description');?></td>
        <td colspan="2"><? $goodsForm->show('p_description_chi');?></td>   
    </tr>
    <tr>
      	<td width="25%"><? $goodsForm->show('p_sid');?></td>
        <td width="25%"><? $goodsForm->show('p_scode');?></td>
        <td width="25%"><? $goodsForm->show('p_cost_rmb');?></td>
        <td width="25%"><? $goodsForm->show('p_cost_remark');?></td>    
    </tr>
    <tr>
      	<td width="25%"><? $goodsForm->show('p_ccode');?></td>
        <td width="25%"><? $goodsForm->show('p_exclusive_to');?></td>
        <td width="25%">&nbsp;<? //$goodsForm->show('p_in_date');?></td>
        <td width="25%">&nbsp;</td>    
    </tr>
</table>
<div class="line"></div>

<?
$goodsForm->show('submitbtn');
?>
</fieldset>
</fieldset>

<?
$goodsForm->end();

}
?>