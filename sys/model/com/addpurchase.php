<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
//201306131746 去除限制
/*if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}*/
		
$goodsForm = new My_Forms();
$formItems = array(

		//應該要加一個 pcid 這樣的不允許重複的 blur事件，來判斷是否輸入的已經在數據庫中存在。應儘量減少提交失敗的情況，否則填半天都白填了。。。！！
		//'pc_pcid' => array('title' => 'Factory PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20/*, 'restrict' => 'judgexid'*/, 'required' => 1),
		'pc_sid' => array('title' => 'Supplier', 'type' => 'select', 'required' => 1, 'options' => $supplier),
		//'pc_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'pc_reference' => array('title' => 'Proforma Invoice #', 'type' => 'text', 'minlen' => 1, 'required' => 1, 'maxlen' => 20),
		'pc_attention' => array('title' => 'Attention', 'type' => 'select', 'required' => 1, 'options' => ''),	
		'pc_customer' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1),
		'pc_customer_po' => array('title' => 'Customer PO#', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		'pc_expected_date' => array('title' => 'ETD', 'type' => 'text', 'restrict' => 'date', 'required' => 1),
		
		'pc_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"'),
		'pc_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),
		
		'pc_packaging' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"'),
		'pc_ship_mark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"'),
		'pc_remarks' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"'),
		
		'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled', 'addon' => 'style="width:130px"'),
		'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled', 'addon' => 'style="width:300px"'),
		'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
		'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),

		'q_pid1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:130px"'),
		'q_p_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		'q_p_quantity1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
		//remark不加了，好像也沒什麼用
		//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		'q_p_description1' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled', 'addon' => 'style="width:300px"'),
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
	
	$pc_pcid = autoGenerationID(); 
	$pc_sid = $_POST['pc_sid']; 
	$pc_send_to = combineSendTo('', $_POST['pc_sid'], $_POST['pc_address']);//$_POST['pc_send_to'];
	$pc_reference = $_POST['pc_reference'];
	$pc_attention = $_POST['pc_attention'];
	$pc_customer = $_POST['pc_customer'];
	$pc_customer_po = $_POST['pc_customer_po'];
	$pc_expected_date = $_POST['pc_expected_date'];
	$pc_remark = $_POST['pc_remark'];
	//这个变量没用的，写着方便计算下面的数（10），不然忘了这个以后麻烦
	$pc_address = $_POST['pc_address'];
	
	//这些是在最后提交的哟
	$pc_packaging = $_POST['pc_packaging'];
	$pc_ship_mark = $_POST['pc_ship_mark'];
	$pc_remarks = $_POST['pc_remarks'];
	
	//有3个 在最後，所以這裡是10，pcid现在是自动生成所以这里变9了
	foreach( $_POST as $v){
		if( $i <= 9){
			$i++;
		}else{
			$q_product[] = $v;	
		}
	}
    //20131013 加in_date旧的mark_date改为保存修改时间
    $in_date = $mark_date = dateMore();
	//$attention = $_SESSION["logininfo"]["aName"];
	
	//$myerror->info($q_product);
	
	//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
	//减3是因为最后還有 packaging, ship mark, remarks
	$q_product_num = intval((count($q_product)-3)/7);
	
	$q_pid = array();
	$q_p_description = array();
	$q_p_quantity = array();
	$q_p_price = array();
	//$q_p_remark = array();
	$q_p_photos = array();
	$q_p_ccode = array();
	$q_p_scode = array();
	
	$total = 0;
	$p_index = 0;
	for($j = 0; $j < $q_product_num; $j++){
		$q_pid[] = $q_product[$p_index++];
		$q_p_description[] = $q_product[$p_index++];
		$q_p_quantity[] = $temp_q = ($q_product[$p_index] != '')?$q_product[$p_index++]:0;
		//mod 20120927 去除钱数中的逗号
		$q_p_price[] = $temp_p = str_replace(',', '', ($q_product[$p_index] != '')?$q_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
		//$q_p_remark[] = $q_product[$p_index++];
		$q_p_photos[] = $q_product[$p_index++];
		$q_p_ccode[] = $q_product[$p_index++];
		$q_p_scode[] = $q_product[$p_index++];
		$total += $temp_q * $temp_p;
	}

    //20130527 加限制，不允许在一个单中添加多个相同PID的item
    if(!check_repeat_item($q_pid)){

        //还不知要怎么算这个
        $ex_total = 0;

        //$myerror->info($q_pid);
        //$myerror->info($q_cost_rmb);
        //$myerror->info($q_p_quantity);
        ////$myerror->info($q_p_remark);
        //$myerror->info($q_p_description);
        //$myerror->info($q_p_photos);
        //$myerror->info($q_p_ccode);
        //$myerror->info($q_p_scode);

        //die();

        //判断是否输入的pcid已存在，因为存在的话由于数据库限制，就会新增失败
        $judge = $mysql->q('select pcid from purchase where pcid = ?', $pc_pcid);
        if(!$judge){
            $result = $mysql->q('insert into purchase (pcid, send_to, attention, reference, remark, in_date, mark_date, total, ex_total, remarks, sid, ship_mark, packaging, customer_po, customer, expected_date, created_by, istatus) values ('.moreQm(18).')', $pc_pcid, $pc_send_to, $pc_attention, $pc_reference, $pc_remark, $in_date, $mark_date, $total, $ex_total, $pc_remarks, $pc_sid, $pc_ship_mark, $pc_packaging, $pc_customer_po, $pc_customer, $pc_expected_date, $_SESSION['logininfo']['aNameChi'], '(D)');

            //20141203 加添加 qc_schedule 记录
            //20141210 改为在approve了后添加 qc_schedule 记录
            //$mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $pc_expected_date, $pc_pcid, $in_date, '', $_SESSION['logininfo']['aName'], '');

            if($result){
                for($k = 0; $k < $q_product_num; $k++){
                    //description 只寫入數據庫中的 description_chi字段，description保持為空
                    $rtn = $mysql->q('insert into purchase_item (pcid, pid, price, quantity, description, description_chi, photos, ccode, scode) values ('.moreQm(9).')', $pc_pcid, $q_pid[$k], $q_p_price[$k], $q_p_quantity[$k], '', $q_p_description[$k], $q_p_photos[$k], $q_p_ccode[$k], $q_p_scode[$k]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_ADD_PURCHASE, $_SESSION["logininfo"]["aName"]." <i>add purchase</i> '".$pc_pcid."' in sys", ACTION_LOG_SYS_ADD_PURCHASE_S, "", "", 0);

                $myerror->ok('新增 Purchase 成功!', 'com-searchpurchase&page=1');
            }else{
                $myerror->error('新增 Purchase 失败', 'BACK');
            }
        }else{
            $myerror->error('输入的 Purchase NO.已存在，新增 Purchase 失败', 'BACK');
        }
    }else{
        $myerror->error('不允许在一个单中添加相同的 Product Item ，新增 Purchase 失败', 'BACK');
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
<h1 class="green">Factory PO<em>* item must be filled in</em></h1>
<fieldset> 
<legend class='legend'>Add Factory PO</legend>

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
  	<td width="25%"><div class="set"><label class="formtitle">Factory PO NO.</label><br />(autogeneration)</div></td>
    <td width="25%"><? $goodsForm->show('pc_reference');?></td>
	<td width="25%"><? $goodsForm->show('pc_sid');?></td>
	<td width="25%"><? $goodsForm->show('pc_attention');?></td>
  </tr>
  <tr>
  	<td width="25%" colspan="2"><? $goodsForm->show('pc_address');?></td>
    <td width="25%" valign="top"><? $goodsForm->show('pc_customer');?></td>
    <td width="25%" valign="top"><? $goodsForm->show('pc_customer_po');?></td>  
  </tr>   
  <tr>
    <td width="25%"><? $goodsForm->show('pc_expected_date');?></td>
  	<td width="25%" colspan="2"><? $goodsForm->show('pc_remark');?></td>
  </tr> 
</table>
<div class="line"></div>
<div style="margin-left:28px;">
<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>
<table width="100%" id="tableDnD">
<tbody id="tbody">
  <tr class="formtitle nodrop nodrag">
  	<td width="3%"></td>
    <td width="13%">Product ID</td>
    <td width="10%" align="center">S Code</td>
    <td width="28%">Description(Chi)</td>   
    <td width="8%">Quantity</td>
    <? /*<td width="20%">Product Remark</td>*/ ?>
    <td width="8%">Cost(RMB)</td>
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
    <td align="center"><div id="scode"></div></td>
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
    <td align="center"><div id="scode1"></div></td>
    <td><? $goodsForm->show('q_p_description1');?></td>
    <td><? $goodsForm->show('q_p_quantity1');?></td>
    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
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
    <td>&nbsp;</td>
    <td align="center">Total: </td>
	<td><div id="total">0</div></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
</table>

<div class="line"></div>
<table width="100%" id="table">
  <tr class="formtitle">
  	<th width="33%">Packaging</th>
    <th width="33%">Ship mark</th>
	<th width="33%">Remarks</th>
  </tr>
  <tr>
  	<td width="33%"><? $goodsForm->show('pc_packaging');?></td>
    <td width="33%"><? $goodsForm->show('pc_ship_mark');?></td>
	<td width="50%"><? $goodsForm->show('pc_remarks');?></td>
  </tr>
</table>
<div class="line"></div>
</div>
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
	$(".template").hide();
	selectSupplier("pc_");
	searchProduct(15, '');
	searchProduct(15, '1');
	//table tr层表单可拖动
	$("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
})
</script>