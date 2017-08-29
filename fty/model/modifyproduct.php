<?php

die('20170318');

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		$rtn = $mysql->q('delete from fty_product where fty_id = ?', $_GET['delid']);
		if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_DEL_PRODUCT, $_SESSION["ftylogininfo"]["aName"]." <i>delete product</i> '".$_GET['delid']."' in fty", ACTION_LOG_FTY_DEL_PRODUCT_S, "", "", 0);

			$myerror->ok('删除产品资料 ('.$_GET['delid'].') 成功!', 'searchproduct&page=1');
		}else{
			$myerror->error('删除产品资料 ('.$_GET['delid'].') 失败', 'searchproduct&page=1');	
		}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$_SESSION['modid'] = $_GET['modid'];
			//普通用户只能搜索到自己开的单
/*			$where_sql = '';
			if (!isFtyAdmin()){
				$where_sql = " AND created_by = '".$_SESSION['ftylogininfo']['aName']."'";
			}*/
            //20130806 加where有多了引号的问题,所以直接在参数里判断了
			$mod_result = $mysql->qone('SELECT * FROM fty_product WHERE fty_id = ? and fty_sid like ?', $_GET['modid'], (isFtyAdmin())?'%%':'%'.$_SESSION['ftylogininfo']['aFtyName'].'%');
			if(!isset($_SESSION['fty_upload_photo_mod']) || $_SESSION['fty_upload_photo_mod'] == ''){
				$_SESSION['fty_upload_photo_mod'] = $mod_result['fty_photo'];
			}
		}/*elseif(isset($_GET['copypid']) && $_GET['copypid'] != ''){
			$_SESSION['modid'] = $_GET['copypid'];
			$mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['copypid']);	
			if( !isset($_SESSION['upload_photo_mod']) || $_SESSION['upload_photo_mod'] == ''){
				$_SESSION['upload_photo_mod'] = $mod_result['photos'];
			}	
			$mod_result['pid'] = '';
		}*/
		
		
				
		$goodsForm = new My_Forms();
        $formItems = array(

            'fty_id' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, /*isset($_GET['copypid'])?'':*/'readonly' => 'readonly', 'value' => isset($mod_result['fty_id'])?$mod_result['fty_id']:''),
            'fty_cost' => array('title' => '价格', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'restrict' => 'number', 'value' => isset($mod_result['fty_cost'])?$mod_result['fty_cost']:'', 'required' => 1),
            'fty_customer_code' => array('title' => '客户编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['fty_customer_code'])?$mod_result['fty_customer_code']:''),
            'fty_desc' => array('title' => '产品描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'required' => 1, 'value' => isset($mod_result['fty_desc'])?$mod_result['fty_desc']:''),

            'fty_type' => array('title' => '类型', 'type' => 'select', 'options' => get_bom_lb(2), 'value' => isset($mod_result['fty_type'])?$mod_result['fty_type']:''),
            'fty_sample_order_no' => array('title' => '板单编号', 'type' => 'select', 'options' => get_sample_order_no_fty(), 'value' => isset($mod_result['fty_sample_order_no'])?$mod_result['fty_sample_order_no']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );
		$goodsForm->init($formItems);
		
		
		if(!$myerror->getAny() && $goodsForm->check()){
			
			$fty_id = $_POST['fty_id'];
			$fty_sid = $_SESSION['ftylogininfo']['aFtyName'];//从session
			$fty_customer_code = $_POST['fty_customer_code'];
			$fty_date = dateMore();
			$fty_desc = $_POST['fty_desc'];
			$fty_cost = $_POST['fty_cost'];
			$fty_photo = isset($_SESSION['fty_upload_photo_mod'])?$_SESSION['fty_upload_photo_mod']:'';
            $fty_type = $_POST['fty_type'];
            $fty_sample_order_no = $_POST['fty_sample_order_no'];
	
			/*
			if(isset($_GET['copypid'])){
				$result = $mysql->q('insert into product (pid, in_date, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos) values ('.moreQm(12).')', $pid, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos);
				//這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
				if($result !== false){
					$myerror->ok('新增产品资料 成功!', 'com-searchproduct&page=1');	
				}else{
					$myerror->error('由于系统原因，新增产品资料 失败', 'BACK');	
				}		
			}else{
				*/
				$result = $mysql->q('update fty_product set fty_type = ?, fty_sample_order_no = ?, fty_desc = ?, fty_cost = ?, fty_customer_code = ?, fty_sid = ?, fty_date = ?, fty_photo = ?, fty_isin = 0 where fty_id = ?', $fty_type, $fty_sample_order_no, $fty_desc, $fty_cost, $fty_customer_code, $fty_sid, $fty_date, $fty_photo, $fty_id);
				//這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
				if($result !== false){
					$_SESSION['fty_upload_photo_mod'] = '';

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['ftylogininfo']['aID'], $ip_real
                        , ACTION_LOG_FTY_MOD_PRODUCT, $_SESSION["ftylogininfo"]["aName"]." <i>modify product</i> '".$_GET['modid']."' in fty", ACTION_LOG_FTY_MOD_PRODUCT_S, "", "", 0);

					$myerror->ok('修改产品资料 ('.$fty_id.') 成功!', 'searchproduct&page=1');	
				}else{
					$myerror->error('由于系统原因，修改产品资料 ('.$fty_id.') 失败', 'BACK');	
				}
			//}
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
	if(isset($_SESSION['fty_upload_photo_mod']) && $_SESSION['fty_upload_photo_mod'] != ''){
        //20130715 管理员取图片的路径与普通用户不同
        if(isFtyAdmin()){
            $pic_path_fty = "upload/fty/".$mod_result['fty_sid'].'/';
        }
		//非要转为GBK，不然中文 getimagesize 就认不出，太坑爹了
		$arr = getimagesize($pic_path_fty . iconv('UTF-8', 'GBK', $_SESSION['fty_upload_photo_mod']));
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(100, 60, $pic_width, $pic_height);
		echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="'.$pic_path_fty . $_SESSION['fty_upload_photo_mod'].'" class="tooltip2" target="_blank" title="'.$_SESSION['fty_upload_photo_mod'].'"><img src="'.$pic_path_fty . $_SESSION['fty_upload_photo_mod'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
		echo "<b><a class='button' href='?act=upload_photo_mod&chg=".$mod_result['fty_photo']."'>更换图片</a></b></div>";
	}else{
		echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=upload_photo_mod'>上传图片</a></div>";
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
	$goodsForm->end();
	
	}
}
?>