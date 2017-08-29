<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
		
$goodsForm = new My_Forms();
$formItems = array(

		//'ci_vid' => array('title' => 'Customs Invoice NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20/*, 'restrict' => 'judgexid'*/, 'required' => 1),
		'ci_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1),
		//'ci_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => '', 'required' => 1),
		'ci_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1),
		'ci_unit' => array('title' => 'Unit', 'type' => 'select', 'options' => get_unit(), 'value' => 'PCS'),
		//'ci_printed_by' => array('title' => 'Printed By', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_packing_num' => array('title' => 'Packing NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'ci_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'ci_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"'),
		'ci_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 2),
		'ci_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => 'THIS INVOICE IS FOR CUSTOMS CLEARANCE PURPOSE ONLY
HS CODE: 7117.1900
MADE IN CHINA'),
		
		'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'ci_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 400, 'rows' => 2, 'disabled' => 'disabled'),
		'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		
		'q_pid1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'q_p_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'ci_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description1' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 400, 'rows' => 2, 'disabled' => 'disabled'),
		'q_p_photos1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),		
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	//$myerror->info($_POST);
	
	$i = 1;//第一个post的是form的标识串，所以会跳过
	$ci_product = array();
	
	$ci_vid = autoGenerationID(); 
	$ci_cid = $_POST['ci_cid']; 
	$ci_send_to = combineSendTo($_POST['ci_cid'], '', $_POST['ci_address']);//$_POST['ci_send_to'];
	$ci_attention = $_POST['ci_attention'];
	$ci_reference = $_POST['ci_reference']; 
	$ci_reference_num = $_POST['ci_reference_num']; 
	$ci_tel = $_POST['ci_tel']; 
	$ci_fax = $_POST['ci_fax'];
	$ci_currency = $_POST['ci_currency'];
	$ci_unit = $_POST['ci_unit'];
	//$ci_printed_by = $_POST['ci_printed_by'];
	$ci_packing_num = $_POST['ci_packing_num'];
	$ci_discount = $_POST['ci_discount'];
	$ci_remark = $_POST['ci_remark'];
	//这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
	$ci_address = $_POST['ci_address'];
	
	//这个是在最后提交的哟
	$ci_remarks = $_POST['ci_remarks'];
	
	//remarks 在最後，所以這裡是14，vid现在是自动生成所以这里变13了
	foreach( $_POST as $v){
		if( $i <= 13){
			$i++;
		}else{
			$ci_product[] = $v;	
		}
	}
	$ci_mark_date = dateMore();
	//暫時不知道這個打印日期是怎麼回事。。。
	$ci_printed_date = dateMore();
	$ci_printed_by = $_SESSION["logininfo"]["aName"];
	
	//$myerror->info($ci_vid);
	
	//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
	//减1是因为最后一个是remarks
	$ci_product_num = intval((count($ci_product)-1)/7);
	
	$ci_pid = array();
	$ci_p_description = array();
	$ci_p_quantity = array();
	$ci_p_price = array();
	//$ci_p_remark = array();
	$ci_p_photos = array();
	$ci_p_ccode = array();
	$ci_p_scode = array();
	
	$p_index = 0;
	for($j = 0; $j < $ci_product_num; $j++){
		$ci_pid[] = $ci_product[$p_index++];
		$ci_p_description[] = $ci_product[$p_index++];
		$ci_p_quantity[] = ($ci_product[$p_index] != '')?$ci_product[$p_index++]:0;
		//mod 20120927 去除钱数中的逗号
		$ci_p_price[] = str_replace(',', '', ($ci_product[$p_index] != '')?$ci_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
		//$ci_p_remark[] = $ci_product[$p_index++];
		$ci_p_photos[] = $ci_product[$p_index++];
		$ci_p_ccode[] = $ci_product[$p_index++];
		$ci_p_scode[] = $ci_product[$p_index++];
	}

    //20130527 加限制，不允许在一个单中添加多个相同PID的item
    if(!check_repeat_item($ci_pid)){

        $total = 0;//算出來的
        $ex_total = 0;//這個也不知道是怎麼得出來的。。。
        $discount = 0;

        //$myerror->info($ci_pid);
        //$myerror->info($ci_cost_rmb);
        //$myerror->info($ci_p_quantity);
        ////$myerror->info($ci_p_remark);
        //$myerror->info($ci_p_description);
        //$myerror->info($ci_p_photos);
        //$myerror->info($ci_p_ccode);
        //$myerror->info($ci_p_scode);

        //die();

        //判断是否输入的vid已存在，因为存在的话由于数据库限制，就会新增失败
        $judge = $mysql->q('select vid from customs_invoice where vid = ?', $ci_vid);
        if(!$judge){
            $result = $mysql->q('insert into customs_invoice (vid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid) values ('.moreQm(19).')', $ci_vid, $ci_send_to, $ci_attention, $ci_tel, $ci_fax, $ci_reference, $ci_remark, $ci_mark_date, $ci_reference_num, $ci_packing_num, $ci_currency, $ci_unit, $ci_printed_by, $ci_printed_date, $total, $ex_total, $discount, $ci_remarks, $ci_cid);
            if($result){
                for($k = 0; $k < $ci_product_num; $k++){
                    $rtn = $mysql->q('insert into customs_invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $ci_pid[$k], $ci_vid, $ci_p_price[$k], $ci_p_quantity[$k], $ci_p_description[$k], $ci_p_photos[$k], $ci_p_ccode[$k], $ci_p_scode[$k]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE, $_SESSION["logininfo"]["aName"]." <i>add customs invoice</i> '".$ci_vid."' in sys", ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_S, "", "", 0);

                $myerror->ok('新增 Customs Invoice 成功!', 'com-searchcustomsinvoice&page=1');
            }else{
                $myerror->error('新增 Customs Invoice 失败', 'BACK');
            }
        }else{
            $myerror->error('输入的 Customs Invoice NO.已存在，新增 Customs Invoice 失败', 'BACK');
        }
    }else{
        $myerror->error('不允许在一个单中添加相同的 Product Item ，新增 Customs Invoice 失败', 'BACK');
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
<h1 class="green">CUSTOMS INVOICE<em>* item must be filled in</em></h1>
<fieldset> 
<legend class='legend'>Add Customs Invoice</legend>

<? /*
<fieldset> 
<legend class='legend'>Selected products</legend>
<?
if( isset($_SESSION['choose']) && !empty($_SESSION['choose'])){
	foreach($_SESSION['choose'] as $v){
		if (is_file($pic_path_com.$v) == true) { 
			$arr = getimagesize($pic_path_com.$v);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(150, 100, $pic_width, $pic_height);
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}else{
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}
	}
}else{
	echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
}
?>
</fieldset>
*/
?>

<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr class="formtitle">
  	<td width="25%"><div class="set"><label class="formtitle">Customs Invoice NO.</label><br />(autogeneration)</div></td>
    <td width="25%"><? $goodsForm->show('ci_cid');?></td>
	<td width="25%"><? $goodsForm->show('ci_attention');?></td>
	<td width="25%"><? $goodsForm->show('ci_tel');?></td>
  </tr>
  <tr>
	<td width="25%" valign="top"><? $goodsForm->show('ci_fax');?></td>
    <td width="25%" colspan="2"><? $goodsForm->show('ci_address');?></td>      
    <td width="25%" valign="top"><? $goodsForm->show('ci_reference_num');?></td>
  </tr> 
  <tr>
  	<td width="25%"><? $goodsForm->show('ci_packing_num');?></td>
	<td width="25%"><? $goodsForm->show('ci_currency');?></td>   
  	<td width="25%"><? $goodsForm->show('ci_unit');?></td>
	<td width="25%"><? $goodsForm->show('ci_reference');?></td>
    <? /*<td width="25%"><? $goodsForm->show('ci_printed_by');?></td>*/?>
  </tr>
  <tr>
  	<td width="25%" colspan="2"><? $goodsForm->show('ci_remark');?></td>
	<td width="25%"><? $goodsForm->show('ci_discount');?></td> 
  </tr>         
</table>
<div class="line"></div>
<div style="margin-left:28px;">
<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>
<table width="100%" id="tableDnD">
<tbody id="tbody">
  <tr class="formtitle nodrop nodrag">
  	<td width="3%"></td>
    <td width="17%">Product ID</td>
    <td width="34%">Description</td>   
    <td width="8%">Quantity</td>
    <? /*<td width="20%">Product Remark</td>*/ ?>
    <td width="8%">Price</td>
    <td width="8%">Subtotal</td>
    <td width="8%" align="center">Photo</td>  
    <? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
    <td width="3%">&nbsp;</td>
	<td width="3%">&nbsp;</td>
<!--    <td width="3%">&nbsp;</td>-->
    <td width="5%">&nbsp;</td>
  </tr>
  <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
  	<td class="dragHandle"></td>
    <td><? $goodsForm->show('q_pid');?></td>
    <td><? $goodsForm->show('q_p_description');?></td>
    <td><? $goodsForm->show('q_p_quantity');?></td>
    <? /*<td><? $goodsForm->show('i_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price');$goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>
    <td id="sub">0</td>
    <td>&nbsp;</td>
	<td><div id="his"></div></td>
<!--    <td><div id="clear"></div></td>-->
    <td><div id="del"></div></td>
  </tr>
  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
  	<td class="dragHandle"></td>
    <td><? $goodsForm->show('q_pid1');?></td>
    <td><? $goodsForm->show('q_p_description1');?></td>
    <td><? $goodsForm->show('q_p_quantity1');?></td>
    <? /*<td><? $goodsForm->show('pi_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price1');$goodsForm->show('q_p_photos1'); $goodsForm->show('q_p_ccode1'); $goodsForm->show('q_p_scode1');?></td>
    <td id="sub">&nbsp;</td>
    <td>&nbsp;</td>
	<td><div id="his1"></div></td>
<!--    <td><div id="clear1"></div></td>-->
    <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
	<td><div id="del1"></div></td>
  </tr> 
  
</tbody> 
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td align="center">Total: </td>
	<td><div id="total">0</div></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
</table>

</div>

<div class="line"></div>
<table>
<tr>
<td width="50%">
<?
$goodsForm->show('ci_remarks');
?>
</td>
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

<script>
$(function(){
	$(".template").hide()
	selectCustomer("ci_")
	searchProduct(17, '')
	searchProduct(17, '1')
	//table tr层表单可拖动
	$("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
	currency("ci_");
})
</script>
