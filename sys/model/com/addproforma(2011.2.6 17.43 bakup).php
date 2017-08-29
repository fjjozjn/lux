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

		'pi_pvid' => array('title' => 'Proforma NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
		'pi_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => $customer, 'required' => 1),
		//'pi_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => '', 'required' => 1),
		'pi_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_currency' => array('title' => 'Currency', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_unit' => array('title' => 'Unit', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		//'pi_printed_by' => array('title' => 'Printed By', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_packing_num' => array('title' => 'Packing NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pi_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'pi_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:400px"'),
		'pi_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),
		'pi_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5),
		
		'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'pi_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 2, 'disabled' => 'disabled'),
		'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	//$myerror->info($_POST);
	
	$i = 1;//第一个post的是form的标识串，所以会跳过
	$pi_product = array();
	
	$pi_pvid = $_POST['pi_pvid']; 
	$pi_cid = $_POST['pi_cid']; 
	$pi_send_to = combineSendTo($_POST['pi_cid'], '', $_POST['pi_address']);//$_POST['pi_send_to'];
	$pi_attention = $_POST['pi_attention'];
	$pi_reference = $_POST['pi_reference']; 
	$pi_reference_num = $_POST['pi_reference_num']; 
	$pi_tel = $_POST['pi_tel']; 
	$pi_fax = $_POST['pi_fax'];
	$pi_currency = $_POST['pi_currency'];
	$pi_unit = $_POST['pi_unit'];
	//$pi_printed_by = $_POST['pi_printed_by'];
	$pi_packing_num = $_POST['pi_packing_num'];
	$pi_discount = $_POST['pi_discount'];
	$pi_remark = $_POST['pi_remark'];
	//这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
	$pi_address = $_POST['pi_address'];
	
	//这个是在最后提交的哟
	$pi_remarks = $_POST['pi_remarks'];
	
	//remarks 在最後，所以這裡是14
	foreach( $_POST as $v){
		if( $i <= 14){
			$i++;
		}else{
			$pi_product[] = $v;	
		}
	}
	$pi_mark_date = dateMore();
	//暫時不知道這個打印日期是怎麼回事。。。
	$pi_printed_date = dateMore();
	$pi_printed_by = $_SESSION["logininfo"]["aName"];
	
	//$myerror->info($pi_pvid);
	
	//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
	//减1是因为最后一个是remarks
	$pi_product_num = intval((count($pi_product)-1)/7);
	
	$pi_pid = array();
	$pi_p_description = array();
	$pi_p_quantity = array();
	$pi_p_price = array();
	//$pi_p_remark = array();
	$pi_p_photos = array();
	$pi_p_ccode = array();
	$pi_p_scode = array();
	
	$p_index = 0;
	for($j = 0; $j < $pi_product_num; $j++){
		$pi_pid[] = $pi_product[$p_index++];
		$pi_p_description[] = $pi_product[$p_index++];
		$pi_p_quantity[] = ($pi_product[$p_index] != '')?$pi_product[$p_index++]:0;
		$pi_p_price[] = ($pi_product[$p_index] != '')?$pi_product[$p_index++]:0;//前後加兩次就出問題了，所以把前面的去掉
		//$pi_p_remark[] = $pi_product[$p_index++];
		$pi_p_photos[] = $pi_product[$p_index++];
		$pi_p_ccode[] = $pi_product[$p_index++];
		$pi_p_scode[] = $pi_product[$p_index++];
	}

	$total = 0;//算出來的
	$ex_total = 0;//這個也不知道是怎麼得出來的。。。
	$discount = 0;
	
	//$myerror->info($pi_pid);
	//$myerror->info($pi_cost_rmb);
	//$myerror->info($pi_p_quantity);
	////$myerror->info($pi_p_remark);
	//$myerror->info($pi_p_description);
	//$myerror->info($pi_p_photos);
	//$myerror->info($pi_p_ccode);
	//$myerror->info($pi_p_scode);
	
	//die();
	
	//判断是否输入的pvid已存在，因为存在的话由于数据库限制，就会新增失败
	$judge = $mysql->q('select pvid from proforma where pvid = ?', $pi_pvid);
	if(!$judge){
		$result = $mysql->q('insert into proforma (pvid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid) values ('.moreQm(19).')', $pi_pvid, $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, $pi_mark_date, $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $discount, $pi_remarks, $pi_cid);
		if($result){
			for($k = 0; $k < $pi_product_num; $k++){
				$rtn = $mysql->q('insert into proforma_item (pid, pvid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $pi_pid[$k], $pi_pvid, $pi_p_price[$k], $pi_p_quantity[$k], $pi_p_description[$k], $pi_p_photos[$k], $pi_p_ccode[$k], $pi_p_scode[$k]);	
			}
			$myerror->ok('新增proforma 成功!', 'com-searchproforma&page=1');	
		}else{
			$myerror->error('新增proforma 失败', 'com-addproforma');	
		}
	}else{
		$myerror->error('输入的Proforma NO.已存在，新增proforma 失败', 'com-addproforma');
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
<h1 class="green">PROFORMA<em>* item must be filled in</em></h1>
<fieldset class="center2col"> 
<legend class='legend'>Add Proforma</legend>

<? /*
<fieldset class="center2col"> 
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
  	<td width="25%"><? $goodsForm->show('pi_pvid');?></td>
    <td width="25%"><? $goodsForm->show('pi_cid');?></td>
	<td width="25%"><? $goodsForm->show('pi_attention');?></td>
	<td width="25%"><? $goodsForm->show('pi_tel');?></td>
  </tr>
  <tr>
  	<td width="25%" valign="top"><? $goodsForm->show('pi_fax');?></td>
    <td width="25%" colspan="2"><? $goodsForm->show('pi_address');?></td>
	<td width="25%" valign="top"><? $goodsForm->show('pi_reference_num');?></td>    
  </tr> 
  <tr>
  	<td width="25%"><? $goodsForm->show('pi_packing_num');?></td>
	<td width="25%"><? $goodsForm->show('pi_currency');?></td>   
  	<td width="25%"><? $goodsForm->show('pi_unit');?></td>
	<td width="25%"><? $goodsForm->show('pi_reference');?></td>
    <? /*<td width="25%"><? $goodsForm->show('pi_printed_by');?></td>*/?>
  </tr>
  <tr>
  	<td width="25%" colspan="2"><? $goodsForm->show('pi_remark');?></td>
	<td width="25%"><? $goodsForm->show('pi_discount');?></td>
  </tr>         
</table>
<div class="line"></div>
<div style="margin-left:28px;">
<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>
<table width="100%" id="table">
<tbody id="tbody">
  <tr class="formtitle">
    <td width="20%">Product ID</td>
    <td width="40%">Description</td>   
    <td width="10%">Quantity</td>
    <? /*<td width="20%">Product Remark</td>*/ ?>
    <td width="10%">Price</td>
    <td width="6%">Subtotal</td>
    <td width="8%" align="center">Photo</td>  
    <td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>
  </tr>
  <tr class="template repeat" valign="top">
    <td><? $goodsForm->show('q_pid');?></td>
    <td><? $goodsForm->show('q_p_description');?></td>
    <td><? $goodsForm->show('q_p_quantity');?></td>
    <? /*<td><? $goodsForm->show('pi_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price');$goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>
    <td id="sub">&nbsp;</td>
    <td>&nbsp;</td>
    <td><div class="del"></div></td>
  </tr>
</tbody> 
<tr>
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
$goodsForm->show('pi_remarks');
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
	selectCustomer("pi_")
	searchProduct(17, '')
})
</script>
