<?

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(

		//'so_no' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1),
		//由send_to 改为sid是为了selectSupplier能通用
		'sid' => array('type' => 'select', 'options' => $supplier_so, 'nostar' => true, 'required' => 1),
		'attention' => array('type' => 'select', 'nostar' => true, 'required' => 1),
		'customer' => array('type' => 'select', 'options' => get_customer(), 'nostar' => true, 'required' => 1),
		'reference' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'etd' => array('type' => 'text', 'restrict' => 'date', 'nostar' => true, 'required' => 1),
		'remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 100),
		'photo_page_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"'),
		'page_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"'),
		'product_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"'),
		'product_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"'),
		'color_total' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'addon' => 'style="width:50px"'),
		'product_each_num' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'nostar' => true, 'required' => 1, 'restrict' => 'number', 'addon' => 'style="width:50px"'),
		'is_change' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) ),
		'select_gold' => array('type' => 'radio', 'options' => array(array('12K金', '12K金'), array('14K金', '14K金'), array('其他', '其他')) ),
		'gold_other' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'select_is_layer' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否'), array('其他', '其他')) ),
		'layer_other' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'select_is_electroplate' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) ),
		'select_is_lead' => array('type' => 'radio', 'options' => array(array('是', '是'), array('否', '否')) ),
		'select_earrings' => array('type' => 'radio', 'options' => array(array('蝴蝶塞', '蝴蝶塞'), array('子弹塞', '子弹塞'), array('飞碟塞', '飞碟塞'), array('透明耳塞', '透明耳塞')) ),
		'packaging_card' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'ring_tag' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'ring_size' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'packaging_require' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'addon' => 'style="width:400px"'),
		'others' => array('type' => 'textarea', 'rows' => 5, 'addon' => 'style="width:400px"'),
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	
	$so_no = autoGenerationID(); //$_POST['so_no']; 
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
	$is_change = isset($_POST['is_change'])?$_POST['is_change']:0;
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
	
	//非输入项
	$creation_date = dateMore();
	$created_by = $_SESSION['logininfo']['aName'];
	

	//判断是否输入的so_no已存在，因为存在的话由于数据库限制，就会新增失败
	$judge = $mysql->q('select id from sample_order where so_no = ?', $so_no);
	if(!$judge){
		$result = $mysql->q('insert into sample_order (so_no, send_to, attention, customer, reference, etd, remark, photo_page_num, page_total, product_each_num, product_num, product_total, color_total, is_change, select_gold, gold_other, select_is_layer, layer_other, select_is_electroplate, select_is_lead, select_earrings, packaging_card, ring_tag, ring_size, packaging_require, others, creation_date, created_by, s_status) values ('.moreQm(29).')', $so_no, $send_to, $attention, $customer, $reference, $etd, $remark, $photo_page_num, $page_total, $product_each_num, $product_num, $product_total, $color_total, $is_change, $select_gold, $gold_other, $select_is_layer, $layer_other, $select_is_electroplate, $select_is_lead, $select_earrings, $packaging_card, $ring_tag, $ring_size, $packaging_require, $others, $creation_date, $created_by, '(I)');
		if($result){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_ADD_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]." <i>add sample order</i> '".$so_no."' in sys", ACTION_LOG_SYS_ADD_SAMPLE_ORDER_S, "", "", 0);

			$myerror->ok('新增 Sample Order 成功!', 'com-searchsample_order&page=1');	
		}else{
			$myerror->error('新增 Sample Order 失败', 'com-addsample_order');	
		}
	}else{
		$myerror->error('输入的 Sample Order NO. 已存在，新增 Sample Order 失败', 'com-addsample_order');
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
	<tr><td class='headertitle' align="center">样板订单</td></tr>
    <tr><td>
        <fieldset class="center2col"> 
        <legend class='legend'>Add Sample Order</legend>
        <table width="100%">
            <tr>
                <td width="15%">致：<h6 class="required">*</h6></td>
                <td width="35%"><? $goodsForm->show('sid');?></td>
                <td width="15%">编号：</td>
                <td width="35%">Autogeneration</td>
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
                <td width="15%">日期：</td>
                <td width="35%">Autogeneration</td>
            </tr>  
            <tr>
                <td width="15%"></td>
                <td width="35%"></td>
                <td width="15%">负责同事：</td>
                <td width="35%">Autogeneration</td>
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
        </table>
        
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?>
        </fieldset>
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