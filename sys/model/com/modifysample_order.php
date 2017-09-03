<?

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//20130807 加copyso_no和appendso_no，原来忘了加
judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'').(isset($_GET['chg_status'])?$_GET['chg_status']:'').(isset($_GET['copyso_no'])?$_GET['copyso_no']:'').(isset($_GET['appendso_no'])?$_GET['appendso_no']:'').(isset($_GET['rev_so_no'])?$_GET['rev_so_no']:'') );

if(isset($_GET['delid']) && $_GET['delid'] != ''){	
	if (!isSysAdmin()){
		$rtn = $mysql->q('update sample_order set s_status = ? where so_no = ?', 'delete', $_GET['delid']);
		if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>delete sample order</i> '".$_GET['delid']."' (change status to delete) in sys", ACTION_LOG_SYS_DEL_SAMPLE_ORDER_S, "", "", 0);

			$myerror->ok('删除 Sample Order 成功!', 'com-searchsample_order&page=1');
		}else{
			$myerror->error('删除 Sample Order 失败!', 'com-searchsample_order&page=1');	
		}
	}else{		
		$rtn2 = $mysql->q('delete from sample_order where so_no = ?', $_GET['delid']);
		if($rtn2){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>delete sample order</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_SAMPLE_ORDER_S, "", "", 0);

			$myerror->ok('删除 Sample Order 成功!', 'com-searchsample_order&page=1');
		}else{
			$myerror->error('删除 Sample Order 失败!', 'com-searchsample_order&page=1');	
		}
	}
}elseif(isset($_GET['chg_status']) && $_GET['chg_status'] != ''){
	$rtn = $mysql->qone('select s_status from sample_order where so_no = ?', $_GET['chg_status']);
    $to_status = '';
	if($rtn['s_status'] == '(I)'){
        $to_status = '(S)';
		$rs = $mysql->q('update sample_order set s_status = ? where so_no = ?', $to_status, $_GET['chg_status']);
	}else{
        $to_status = '(I)';
        $rs = $mysql->q('update sample_order set s_status = ? where so_no = ?', $to_status, $_GET['chg_status']);
	}
	if($rs){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_CHANGE_SAMPLE_ORDER_STATUS, $_SESSION["logininfo"]["aName"]." <i>change sample order status</i> '".$_GET['chg_status']."' from '".$rtn['s_status']."' to '".$to_status."' in sys", ACTION_LOG_SYS_CHANGE_SAMPLE_ORDER_STATUS_S, "", "", 0);

		$myerror->ok('更改 Sample Order 状态成功!', 'com-searchsample_order&page=1');
	}else{
		$myerror->error('更改 Sample Order 状态失败!', 'com-searchsample_order&page=1');	
	}
}else{
    $no = '';
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM sample_order WHERE so_no = ?', $_GET['modid']);	
		//因為一開始沒有attention的選項所以要加上
		$mod_supplier_contact = array(array($mod_result['attention'], $mod_result['attention']));
		
		//20121011 加显示此 sample_order 包含的 product ID 
		$mysql->q('select pid, photos from product where sample_order_no = ?', $_GET['modid']);
		$product_rtn = $mysql->fetch();
        $no = $_GET['modid'];
	}elseif(isset($_GET['copyso_no']) && $_GET['copyso_no'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM sample_order WHERE so_no = ?', $_GET['copyso_no']);
		//因為一開始沒有attention的選項所以要加上
		$mod_supplier_contact = array(array($mod_result['attention'], $mod_result['attention']));
        //20130828 设置为当天日期
        $mod_result['creation_date'] = date('Y-m-d');
        $no = $_GET['copyso_no'];
	}elseif(isset($_GET['appendso_no']) && $_GET['appendso_no'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM sample_order WHERE so_no = ?', $_GET['appendso_no']);
		//因為一開始沒有attention的選項所以要加上
		$mod_supplier_contact = array(array($mod_result['attention'], $mod_result['attention']));
        //20130828 设置为当天日期
        $mod_result['creation_date'] = date('Y-m-d');
        $no = $_GET['appendso_no'];
	}elseif(isset($_GET['rev_so_no']) && $_GET['rev_so_no'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM sample_order WHERE so_no = ?', $_GET['rev_so_no']);
        //因為一開始沒有attention的選項所以要加上
        $mod_supplier_contact = array(array($mod_result['attention'], $mod_result['attention']));
        //20130828 设置为当天日期
        $mod_result['creation_date'] = date('Y-m-d');
        $no = $_GET['rev_so_no'];
    }else{
		die('Need modid!');	
	}
	
	$goodsForm = new My_Forms();
	
	$formItems = array(
			'sid' => array('type' => 'select', 'options' => $supplier_so, 'required' => 1, 'nostar' => true, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
			'attention' => array('type' => 'select', 'options' => $mod_supplier_contact, 'required' => 1, 'nostar' => true, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
			'customer' => array('type' => 'select', 'options' => get_customer(), 'required' => 1, 'nostar' => true, 'value' => isset($mod_result['customer'])?$mod_result['customer']:''),
			'reference' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
			'etd' => array('type' => 'text', 'restrict' => 'date', 'nostar' => true, 'required' => 1, 'value' => isset($mod_result['etd'])?date('Y-m-d', strtotime($mod_result['etd'])):''),
			'remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
			'photo_page_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"', 'value' => isset($mod_result['photo_page_num'])?$mod_result['photo_page_num']:''),
			'page_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"', 'value' => isset($mod_result['page_total'])?$mod_result['page_total']:''),
			'product_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"', 'value' => isset($mod_result['product_num'])?$mod_result['product_num']:''),
			'product_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"', 'value' => isset($mod_result['product_total'])?$mod_result['product_total']:''),
			'color_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"', 'value' => isset($mod_result['color_total'])?$mod_result['color_total']:''),
			'product_each_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'restrict' => 'number', 'addon' => 'style="width:50px"', 'value' => isset($mod_result['product_each_num'])?$mod_result['product_each_num']:''),
			'is_change' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) , 'value' => isset($mod_result['is_change'])?$mod_result['is_change']:''),
			'select_gold' => array('type' => 'radio', 'options' => array(array('12K金', '12K金'), array('14K金', '14K金'), array('其他', '其他')) , 'value' => isset($mod_result['select_gold'])?$mod_result['select_gold']:''),
			'gold_other' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['gold_other'])?$mod_result['gold_other']:''),
			'select_is_layer' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否'), array('其他', '其他')) , 'value' => isset($mod_result['select_is_layer'])?$mod_result['select_is_layer']:''),
			'layer_other' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['layer_other'])?$mod_result['layer_other']:''),
			'select_is_electroplate' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) , 'value' => isset($mod_result['select_is_electroplate'])?$mod_result['select_is_electroplate']:''),
			'select_is_lead' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) , 'value' => isset($mod_result['select_is_lead'])?$mod_result['select_is_lead']:''),
			'select_earrings' => array('type' => 'radio', 'options' => array(array('蝴蝶塞', '蝴蝶塞'), array('子弹塞', '子弹塞'), array('飞碟塞', '飞碟塞'), array('透明耳塞', '透明耳塞')) , 'value' => isset($mod_result['select_earrings'])?$mod_result['select_earrings']:''),
			'packaging_card' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['packaging_card'])?$mod_result['packaging_card']:''),
			'ring_tag' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['ring_tag'])?$mod_result['ring_tag']:''),
			'ring_size' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['ring_size'])?$mod_result['ring_size']:''),
			'packaging_require' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['packaging_require'])?$mod_result['packaging_require']:''),
			'others' => array('type' => 'textarea', 'rows' => 5, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['others'])?$mod_result['others']:''),
			
			'creation_date' => array('type' => 'text', 'restrict' => 'date', 'nostar' => true, 'required' => 1, 'value' => isset($mod_result['creation_date'])?date('Y-m-d', strtotime($mod_result['creation_date'])):''),
			'created_by' => array('type' => 'select', 'required' => 1, 'options' => $user, 'nostar' => true, 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:''),
					
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);		
		
	if(isset($_GET['modid'])){
		$formItems['so_no'] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'nostar' => true, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['so_no'])?$mod_result['so_no']:'');
	}	
				
	$goodsForm->init($formItems);
	
	
	if(!$myerror->getAny() && $goodsForm->check()){

        $so_no = '';
        if(isset($_GET['modid'])){
            $so_no = $_POST['so_no'];
        }elseif(isset($_GET['copyso_no'])){
            $so_no = autoGenerationID();
        }elseif(isset($_GET['appendso_no'])){
            $so_no = autoGenerationAddID($_GET['appendso_no']);
        }elseif(isset($_GET['rev_so_no'])){
            $so_no = autoGenerationAddID($_GET['rev_so_no'], 'rev');
        }

        //20170831
        $add_tip = '';
        $file_target = '';
        $file_date = date('YmdHis');
        if( (@$_FILES['sample_order_file']['type'] == 'application/pdf' && (@$_FILES['sample_order_file']['size'] / 1024) <= 10240) || @$_FILES['sample_order_file']['name'] == '' ) {
            if (@$_FILES['sample_order_file']['error'] > 0 && @$_FILES['sample_order_file']['error'] != 4){
                $myerror->error('Upload file failure ! Return Code: '.@$_FILES['sample_order_file']['error'], 'BACK');
            }else {
                if (@$_FILES['sample_order_file']['name'] != '') {
                    // 转为大写字母(pid + 时间 + 原图片的后缀名)
                    $temp = end(explode('.', @$_FILES['sample_order_file']['name']));
                    $file_target = strtoupper($so_no . '_' . $file_date .'.'. $temp);
                    //上传图片
                    move_uploaded_file(@$_FILES['sample_order_file']['tmp_name'], iconv('UTF-8', 'GBK', $sample_order_file_path_com . $file_target));
                    if (file_exists(iconv('UTF-8', 'GBK', $sample_order_file_path_com . $file_target))) {
                        $add_tip .= 'Upload file ' . $file_target . ' success! ';
                    } else {
                        $add_tip .= 'Upload file ' . $file_target . ' <i>failure</i>! ';
                    }
                }
            }
        }else{
            $myerror->error('上传文件 失败! 请选择PDF格式的文件上传! 且文件大小不要超过 10 MB!', 'BACK');
        }

		$send_to = $_POST['sid'];
		$attention = $_POST['attention'];
		$customer = $_POST['customer'];
		$reference = $_POST['reference'];
		$etd = $_POST['etd'];
		$remark = $_POST['remark'];
		$photo_page_num = $_POST['photo_page_num'];
		$page_total = $_POST['page_total'];
		$product_each_num = $_POST['product_each_num']; 
		$product_num = $_POST['product_num'];
		$product_total = $_POST['product_total'];
		$color_total = $_POST['color_total'];
		$is_change = isset($_POST['is_change'])?$_POST['is_change']:'';
		$select_gold = isset($_POST['select_gold'])?$_POST['select_gold']:'';
		$gold_other = $_POST['gold_other'];
		$select_is_layer = isset($_POST['select_is_layer'])?$_POST['select_is_layer']:'';
		$layer_other = $_POST['layer_other'];
		$select_is_electroplate = isset($_POST['select_is_electroplate'])?$_POST['select_is_electroplate']:''; 
		$select_is_lead = isset($_POST['select_is_lead'])?$_POST['select_is_lead']:'';
		$select_earrings = isset($_POST['select_earrings'])?$_POST['select_earrings']:'';
		$packaging_card = $_POST['packaging_card'];
		$ring_tag = $_POST['ring_tag'];
		$ring_size = $_POST['ring_size'];
		$packaging_require = $_POST['packaging_require'];
		$others = $_POST['others'];
		
		//如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00:00:00		
		$creation_date = ((date('Y-m-d', strtotime($mod_result['creation_date'])) == $_POST['creation_date'])?$mod_result['creation_date']:$_POST['creation_date'].' 00:00:00');
		$created_by = $_POST['created_by'];

        $sample_order_file = $file_target;

		if(isset($_GET['copyso_no']) && $_GET['copyso_no'] != ''){
			$result = $mysql->q('insert into sample_order (so_no, send_to, attention, customer, reference, etd, remark, photo_page_num, page_total, product_each_num, product_num, product_total, color_total, is_change, select_gold, gold_other, select_is_layer, layer_other, select_is_electroplate, select_is_lead, select_earrings, packaging_card, ring_tag, ring_size, packaging_require, others, creation_date, created_by, s_status, sample_order_file) values ('.moreQm(30).')', $so_no, $send_to, $attention, $customer, $reference, $etd, $remark, $photo_page_num, $page_total, $product_each_num, $product_num, $product_total, $color_total, $is_change, $select_gold, $gold_other, $select_is_layer, $layer_other, $select_is_electroplate, $select_is_lead, $select_earrings, $packaging_card, $ring_tag, $ring_size, $packaging_require, $others, $creation_date, $created_by, '(I)', $sample_order_file);
			if($result){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_COPY_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>copy sample order</i> from '".$_GET['copyso_no']."' to '".$so_no."' in sys", ACTION_LOG_SYS_COPY_SAMPLE_ORDER_S, "", "", 0);

				$myerror->ok('新增 Sample Order 成功!', 'com-searchsample_order&page=1');	
			}else{
				$myerror->error('新增 Sample Order 失败', 'com-searchsample_order&page=1');	
			}
		}elseif(isset($_GET['modid']) && $_GET['modid'] != ''){		
			$result = $mysql->q('update sample_order set send_to = ?, attention = ?, customer = ?, reference = ?, etd = ?, remark = ?, photo_page_num = ?, page_total = ?, product_each_num = ?, product_num = ?, product_total = ?, color_total = ?, is_change = ?, select_gold = ?, gold_other = ?, select_is_layer = ?, layer_other = ?, select_is_electroplate = ?, select_is_lead = ?, select_earrings = ?, packaging_card = ?, ring_tag = ?, ring_size = ?, packaging_require = ?, others = ?, creation_date = ?, created_by = ?, sample_order_file = ? where so_no = ?', $send_to, $attention, $customer, $reference, $etd, $remark, $photo_page_num, $page_total, $product_each_num, $product_num, $product_total, $color_total, $is_change, $select_gold, $gold_other, $select_is_layer, $layer_other, $select_is_electroplate, $select_is_lead, $select_earrings, $packaging_card, $ring_tag, $ring_size, $packaging_require, $others, $creation_date, $created_by, $sample_order_file, $so_no);
			if($result){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>modify sample order</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_SAMPLE_ORDER_S, "", "", 0);

				$myerror->ok('修改 Sample Order 成功!', 'com-searchsample_order&page=1');	
			}else{
				$myerror->error('修改 Sample Order 失败', 'com-searchsample_order&page=1');	
			}
		}elseif(isset($_GET['appendso_no']) && $_GET['appendso_no'] != ''){
			$result = $mysql->q('insert into sample_order (so_no, send_to, attention, customer, reference, etd, remark, photo_page_num, page_total, product_each_num, product_num, product_total, color_total, is_change, select_gold, gold_other, select_is_layer, layer_other, select_is_electroplate, select_is_lead, select_earrings, packaging_card, ring_tag, ring_size, packaging_require, others, creation_date, created_by, s_status, sample_order_file) values ('.moreQm(30).')', $so_no, $send_to, $attention, $customer, $reference, $etd, $remark, $photo_page_num, $page_total, $product_each_num, $product_num, $product_total, $color_total, $is_change, $select_gold, $gold_other, $select_is_layer, $layer_other, $select_is_electroplate, $select_is_lead, $select_earrings, $packaging_card, $ring_tag, $ring_size, $packaging_require, $others, $creation_date, $created_by, '(I)', $sample_order_file);
			if($result){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_APPEND_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>add sample order</i> from '".$_GET['appendso_no']."' to '".$so_no."' in sys", ACTION_LOG_SYS_APPEND_SAMPLE_ORDER_S, "", "", 0);

				$myerror->ok('Sample Order 加单成功!', 'com-searchsample_order&page=1');	
			}else{
				$myerror->error('Sample Order 加单失败', 'com-searchsample_order&page=1');	
			}			
		}elseif($_GET['rev_so_no'] && $_GET['rev_so_no'] != ''){
            $result = $mysql->q('insert into sample_order (so_no, send_to, attention, customer, reference, etd, remark, photo_page_num, page_total, product_each_num, product_num, product_total, color_total, is_change, select_gold, gold_other, select_is_layer, layer_other, select_is_electroplate, select_is_lead, select_earrings, packaging_card, ring_tag, ring_size, packaging_require, others, creation_date, created_by, s_status, sample_order_file) values ('.moreQm(30).')', $so_no, $send_to, $attention, $customer, $reference, $etd, $remark, $photo_page_num, $page_total, $product_each_num, $product_num, $product_total, $color_total, $is_change, $select_gold, $gold_other, $select_is_layer, $layer_other, $select_is_electroplate, $select_is_lead, $select_earrings, $packaging_card, $ring_tag, $ring_size, $packaging_require, $others, $creation_date, $created_by, '(I)', $sample_order_file);
            if($result){
                $myerror->ok('Sample Order 加改版单成功!', 'com-searchsample_order&page=1');
            }else{
                $myerror->error('Sample Order 加改版单失败', 'com-searchsample_order&page=1');
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
<!--h1 class="green">SAMPLE ORDER<em>* item must be filled in</em></h1-->

<?php
$goodsForm->begin();
?>
<table width="75%" id="table" class="formtitle" align="center">
	<tr><td class='headertitle' align="center"><?php if(strpos($no, 'REV')){ echo '改版单'; }else{ echo '样板订单'; }?></td></tr>
    <tr><td>
	<? if(isset($_GET['modid'])){ ?>
		<fieldset class="center2col"> 
		<legend class='legend'>Action</legend>
			<a class="button" href="model/com/sample_order_pdf.php?so_no=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>
			<a class="button" href="?act=com-modifysample_order&copyso_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>COPY</b></a>
            <a class="button" href="?act=com-modifysample_order&appendso_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>加单</b></a>
            <a class="button" href="?act=com-modifysample_order&rev_so_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>改版单</b></a>
            <a class="button" href="?act=com-modifyproforma&so_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>ADD TO PROFORMA</b></a>
		</fieldset>
	<? }?>
        <fieldset class="center2col"> 
        <legend class='legend'>Modify Sample Order</legend>
        <table width="100%">
            <tr>
                <td width="15%">致：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('sid');?></td>
                <td width="15%">编号：</td>
                <td width="35%"><? if(isset($_GET['modid'])) $goodsForm->show('so_no');else echo 'Autogeneration'; ?></td>
            </tr>
            <tr>
                <td width="15%">收件人：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('attention');?></td>
                <td width="15%">客户：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('customer');?></td>
            </tr>
            <tr>
                <td width="15%">参考：</td>
                <td width="35%"><? $goodsForm->show('reference');?></td>
                <td width="15%">要求出货日期：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('etd');?></td>
            </tr>
            <tr>
                <td width="15%">备注：</td>
                <td width="35%"><? $goodsForm->show('remark');?></td>
                <td width="15%">日期：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('creation_date');?></td>
            </tr>
            <tr>
                <td width="15%"></td>
                <td width="35%"></td>
                <td width="15%">负责同事：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('created_by');?></td>
            </tr>
        </table>
        <br />
        <table class="formtitle">
            <tr>
                <tr><td>1）影印图&nbsp;</td><td><? $goodsForm->show('photo_page_num');?></td><td>&nbsp;页， </td><td>连此页 </td><td><? $goodsForm->show('page_total');?></td><td>&nbsp;页</td></tr> 
                <tr><td>2）共&nbsp;</td><td><? $goodsForm->show('product_total');?></td><td>&nbsp;款，每款</td><td><? $goodsForm->show('color_total');?></td><td>&nbsp;色。每款每色&nbsp;</td><td><? $goodsForm->show('product_each_num');?></td><td>&nbsp;件， </td><td>连深圳留底板各&nbsp;</td><td><? $goodsForm->show('product_num');?></td><td>&nbsp;件<h6 class="required">*</h6></td></tr> 
            </tr>
        </table>
        
        <table class="formtitle">
            <tr>
                <td>3）细节要求：</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;a.&nbsp;&nbsp;单内凡改板款做公司办：</td><td><? $goodsForm->show('is_change');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;b.&nbsp;&nbsp;金色系列：</td><td><? $goodsForm->show('select_gold');?></td><td><? $goodsForm->show('gold_other');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;c.&nbsp;&nbsp;光金是否加保护层：</td><td><? $goodsForm->show('select_is_layer');?></td><td><? $goodsForm->show('layer_other');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;d.&nbsp;&nbsp;是否做无叻电镀：</td><td><? $goodsForm->show('select_is_electroplate');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;e.&nbsp;&nbsp;是否做无铅：</td><td><? $goodsForm->show('select_is_lead');?></td>
            </tr>	
            <tr>
                <td>&nbsp;&nbsp;f.&nbsp;&nbsp;耳针配套耳塞：</td><td><? $goodsForm->show('select_earrings');?></td>
            </tr>					
        </table>
                
        <table class="formtitle">
            <tr>
                <td>4）包装：</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;a.&nbsp;&nbsp;包装卡：</td><td><? $goodsForm->show('packaging_card');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;b.&nbsp;&nbsp;戒子标签：</td><td><? $goodsForm->show('ring_tag');?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;c.&nbsp;&nbsp;戒子尺码：</td><td><? $goodsForm->show('ring_size');?></td>
            </tr>
            <tr>
                <td valign="top">&nbsp;&nbsp;d.&nbsp;&nbsp;包装要求：</td><td><? $goodsForm->show('packaging_require');?></td>
            </tr>				
        </table>
        
        <table class="formtitle">
            <tr>
                <td>5）其他：</td>
            </tr>
            <tr>
                <td><? $goodsForm->show('others');?></td>
            </tr>
            <tr>
                <td>6）Product ID：</td>
            </tr>            
            <tr>
            <? if(isset($product_rtn) && $product_rtn != ''){ ?>
            	<td><?
				 	foreach($product_rtn as $v) {
                        echo '<div style="float:left">';
                        if ($v['photos']) {
                            echo '<a href="/sys/upload/lux/' . $v['photos'] . '" target="_blank" title="' . $v['photos'] . '"><img src="/sys/upload/luxsmall/s_' . $v['photos'] . '" border="0" align="middle" width="80" height="60"></a>';
                        } else {
                            echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60">';
                        }
                        echo '<br /><a target="_blank" href="?act=com-modifyproduct_new&modid=' . $v['pid'] . '">' . $v['pid'] . '</a></div>';
                    }
				 	?></td>                
            <? }else{ ?>
            	<td>
                	无
                </td>
            <? } ?>
            </tr>
            <tr>
                <td>7）上传Product PDF：</td>
            </tr>
            <tr>
                <td><input type='file' name='sample_order_file' id='sample_order_file' /></td>
            </tr>
        </table>
        
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?>         
        </fieldset>
		
		<? if(isset($_GET['modid'])){ ?>
		<fieldset class="center2col"> 
		<legend class='legend'>Action</legend>
			<a class="button" href="model/com/sample_order_pdf.php?so_no=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>
			<a class="button" href="?act=com-modifysample_order&copyso_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>COPY</b></a>
            <a class="button" href="?act=com-modifysample_order&appendso_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>加单</b></a>
            <a class="button" href="?act=com-modifyproforma&so_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>ADD TO PROFORMA</b></a>
		</fieldset>
		<? }?>
		
    </td></tr>
</table>        

<?
$goodsForm->end();
}
?>

<script>
$(function(){
    selectSampleOrder('')
})
</script>