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

		//'q_qid' => array('title' => 'Quotation NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20/*, 'restrict' => 'judgexid'*/, 'required' => 1),
		'q_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
		'q_attention' => array('title' => 'Attention', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'q_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),	
		'q_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1),
		'q_unit' => array('title' => 'Unit', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'q_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'q_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"'),
		'q_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5),
		
		'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
		'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),

		'q_pid1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'q_p_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description1' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
		'q_p_photos1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	//$myerror->info($_POST);
	
	$i = 1;//第一个post的是form的标识串，所以会跳过
	$q_product = array();
	
	$q_qid = autoGenerationID(); 
	$q_send_to = $_POST['q_send_to'];
	$q_attention = $_POST['q_attention'];
	$q_reference = $_POST['q_reference'];
	$q_currency = $_POST['q_currency'];
	$q_unit = $_POST['q_unit'];
	$q_discount = $_POST['q_discount'];
	$q_remark = $_POST['q_remark'];
	
	//这个是在最后提交的哟
	$q_remarks = $_POST['q_remarks'];
	
	//remarks 在最後，所以這裡是9，qid现在是自动生成所以这里变8了
	foreach( $_POST as $v){
		if( $i <= 8){
			$i++;
		}else{
			$q_product[] = $v;	
		}
	}
	$mark_date = dateMore();
	//$attention = $_SESSION["logininfo"]["aName"];
	
	//$myerror->info($q_qid);
	
	//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
	//减1是因为最后一个是remarks
	$q_product_num = intval((count($q_product)-1)/7);
	
	$q_pid = array();
	$q_p_description = array();
	$q_p_quantity = array();
	$q_p_price = array();
	//$q_p_remark = array();
	$q_p_photos = array();
	$q_p_ccode = array();
	$q_p_scode = array();
	
	$p_index = 0;
	for($j = 0; $j < $q_product_num; $j++){
		$q_pid[] = $q_product[$p_index++];
		$q_p_description[] = $q_product[$p_index++];
		$q_p_quantity[] = ($q_product[$p_index] != '')?$q_product[$p_index++]:0;
		//mod 20120927 去除钱数中的逗号
		$q_p_price[] = str_replace(',', '', ($q_product[$p_index] != '')?$q_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
		//$q_p_remark[] = $q_product[$p_index++];
		$q_p_photos[] = $q_product[$p_index++];
		$q_p_ccode[] = $q_product[$p_index++];
		$q_p_scode[] = $q_product[$p_index++];
	}

    //20130527 加限制，不允许在一个单中添加多个相同PID的item
    if(!check_repeat_item($q_pid)){
        $total = 0;//算出來的
        $discount = 0;

        //$myerror->info($q_pid);
        //$myerror->info($q_cost_rmb);
        //$myerror->info($q_p_quantity);
        ////$myerror->info($q_p_remark);
        //$myerror->info($q_p_description);
        //$myerror->info($q_p_photos);
        //$myerror->info($q_p_ccode);
        //$myerror->info($q_p_scode);

        //die();

        //判断是否输入的qid已存在，因为存在的话由于数据库限制，就会新增失败
        $judge = $mysql->q('select qid from quotation where qid = ?', $q_qid);
        if(!$judge){
            $result = $mysql->q('insert into quotation (qid, send_to, attention, reference, remark, mark_date, currency, unit, total, discount, remarks, created_by) values ('.moreQm(12).')', $q_qid, $q_send_to, $q_attention, $q_reference, $q_remark, $mark_date, $q_currency, $q_unit, $total, $discount, $q_remarks, $_SESSION['logininfo']['aName']);
            if($result){
                for($k = 0; $k < $q_product_num; $k++){
                    $rtn = $mysql->q('insert into quote_item (pid, qid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $q_pid[$k], $q_qid, $q_p_price[$k], $q_p_quantity[$k], $q_p_description[$k], $q_p_photos[$k], $q_p_ccode[$k], $q_p_scode[$k]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_ADD_QUOTATION, $_SESSION["logininfo"]["aName"]." <i>add quotation</i> '".$q_qid."' in sys", ACTION_LOG_SYS_ADD_QUOTATION_S, "", "", 0);

                $myerror->ok('新增 Quotation 成功!', 'com-searchquotation&page=1');
            }else{
                $myerror->error('新增 Quotation 失败', 'BACK');
            }
        }else{
            $myerror->error('输入的 Quotation NO.已存在，新增 Quotation 失败', 'BACK');
        }
    }else{
        $myerror->error('不允许在一个单中添加相同的 Product Item ，新增 Quotation 失败', 'BACK');
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
<h1 class="green">QUOTATION<em>* item must be filled in</em></h1>
<fieldset> 
<legend class='legend'>Add Quotation</legend>

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
  	<td width="25%"><div class="set"><label class="formtitle">Quotation NO.</label><br />(autogeneration)</div></td>
    <td width="25%"><? $goodsForm->show('q_send_to');?></td>
	<td width="25%"><? $goodsForm->show('q_attention');?></td>
    <td width="25%"><? $goodsForm->show('q_reference');?></td>
  </tr>
  <tr>
    <td width="25%"><? $goodsForm->show('q_currency');?></td>
  	<td width="25%"><? $goodsForm->show('q_unit');?></td>
    <td width="25%"><? $goodsForm->show('q_discount');?></td>
  </tr> 
  <tr>
  	<td colspan="2"><? $goodsForm->show('q_remark');?></td>
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
    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
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
    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price1');$goodsForm->show('q_p_photos1'); $goodsForm->show('q_p_ccode1'); $goodsForm->show('q_p_scode1');?></td>
    <td id="sub">&nbsp;</td>
    <td>&nbsp;</td>
	<td><div id="his1"></div></td> 
<!--    <td><div id="clear1"></div></td> -->
    <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
	<td><div id="del1"></div></td>  
	</tr>  
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
$goodsForm->show('q_remarks');
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
	searchProduct(11, '')
	searchProduct(11, '1')
	//table tr层表单可拖动
	$("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
	currency("q_");
})
</script>