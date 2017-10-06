<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	//由於指定了foreign key，所以要先刪quote_item裏的內容
	$rtn1 = $mysql->q('delete from quote_item where qid = ?', $_GET['delid']);
	$rtn2 = $mysql->q('delete from quotation where qid = ?', $_GET['delid']);
	if($rtn2){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_DEL_QUOTATION, $_SESSION["logininfo"]["aName"]." <i>delete quotation</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_QUOTATION_S, "", "", 0);

		$myerror->ok('删除 Quotation 成功!', 'com-searchquotation&page=1');
	}else{
		$myerror->error('删除 Quotation 失败!', 'com-searchquotation&page=1');
	}
}else{
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM quotation WHERE qid = ?', $_GET['modid']);	
		$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, quote_item q WHERE  p.pid = q.pid AND q.qid = ?', $_GET['modid']);
		$q_item_rtn = $mysql->fetch();
		//$myerror->info($q_item_rtn);die();
		$q_item_num = count($q_item_rtn);
		//$myerror->info($q_item_num);
		//currency session
		//$_SESSION['currency'] = $mod_result['currency'];
	}else{
		die('Need modid!');	
	}
	
	
			
	$goodsForm = new My_Forms();
	$formItems = array(
			
			'q_qid' => array('title' => 'Quotation NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['qid'])?$mod_result['qid']:''),
			'q_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
			'q_attention' => array('title' => 'Attention', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
			'q_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
			'q_mark_date' => array('title' => 'Creation Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['mark_date'])?date('Y-m-d', strtotime($mod_result['mark_date'])):''),		
			'q_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),
			'q_unit' => array('title' => 'Unit', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
			'q_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['discount'])?intval($mod_result['discount']):''),
			'q_created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => get_user('sys'), 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:''),
			
			'q_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
			'q_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['remarks'])?$mod_result['remarks']:''),
			
			'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
			'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
			'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
			//remark不加了，好像也沒什麼用
			//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
			'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
			'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
			'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
			'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
						
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
			);
	
	//第一個上面用了
	//原来从1开始，现在从0开始
	for($i = 0; $i < $q_item_num; $i++){
		$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($q_item_rtn[$i]['pid'])?$q_item_rtn[$i]['pid']:'', 'readonly' => 'readonly');
		$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($q_item_rtn[$i]['price'])?formatMoney($q_item_rtn[$i]['price']):'');
		$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($q_item_rtn[$i]['quantity']) && ($q_item_rtn[$i]['quantity'] == 0 || $q_item_rtn[$i]['quantity'] == ''))?1:intval($q_item_rtn[$i]['quantity']));
		$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($q_item_rtn[$i]['description'])?$q_item_rtn[$i]['description']:'');
		$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($q_item_rtn[$i]['photos'])?$q_item_rtn[$i]['photos']:'');
		$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($q_item_rtn[$i]['ccode'])?$q_item_rtn[$i]['ccode']:'');
		$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($q_item_rtn[$i]['scode'])?$q_item_rtn[$i]['scode']:'');
	}
	
	//最后一个
	$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20);
	$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
	$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
	//remark不加了，好像也沒什麼用
	//'pi_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
	$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled');
	$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
	$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
	$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');	
	
	//$myerror->info($formItems);
	//die();
			
	$goodsForm->init($formItems);
	
	
	if(!$myerror->getAny() && $goodsForm->check()){
		//$myerror->info($_POST);
		
		$i = 1;//第一个post的是form的标识串，所以会跳过
		$q_product = array();
		
		$q_qid = $_POST['q_qid']; 
		$q_send_to = $_POST['q_send_to'];
		$q_attention = $_POST['q_attention'];
		$q_reference = $_POST['q_reference'];
		$q_currency = $_POST['q_currency'];
		$q_unit = $_POST['q_unit'];
		$q_discount = $_POST['q_discount'];
		$q_remark = $_POST['q_remark'];
		
		//这个是在最后提交的哟
		$q_remarks = $_POST['q_remarks'];
		$q_created_by = $_POST['q_created_by'];

		//remarks 在最後，所以這裡是8，有多了個mark_date所以是9了，又多了attention，所以现在是10了，多了created_by，所以现在是11了
		foreach( $_POST as $v){
			if( $i <= 11){
				$i++;
			}else{
				$q_product[] = $v;	
			}
		}
		//如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00：00：00
		$mark_date = (date('Y-m-d', strtotime($mod_result['mark_date'])) == $_POST['q_mark_date'])?$mod_result['mark_date']:$_POST['q_mark_date'].' 00:00:00';
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

            $result = $mysql->q('update quotation set qid = ?, send_to = ?, attention = ?, reference = ?, remark = ?, mark_date = ?, currency = ?, unit = ?, total = ?, discount = ?, remarks = ?, created_by = ? where qid = ?', $q_qid, $q_send_to, $q_attention, $q_reference, $q_remark, $mark_date, $q_currency, $q_unit, $total, $q_discount, $q_remarks, $q_created_by, $_GET['modid']);
            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){
                $rtn = $mysql->q('delete from quote_item where qid = ?', $_GET['modid']);
                for($k = 0; $k < $q_product_num; $k++){
                    $rtn = $mysql->q('insert into quote_item (pid, qid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $q_pid[$k], $q_qid, $q_p_price[$k], $q_p_quantity[$k], $q_p_description[$k], $q_p_photos[$k], $q_p_ccode[$k], $q_p_scode[$k]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_QUOTATION, $_SESSION["logininfo"]["aName"]." <i>modify quotation</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_QUOTATION_S, "", "", 0);

                $myerror->ok('修改 Quotation 成功!', 'BACK');
            }else{
                $myerror->error('修改 Quotation 失败', 'BACK');
            }
        }else{
            $myerror->error('不允许在一个单中添加相同的 Product Item ，新增或修改 Quotation 失败', 'BACK');
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
	
<h1 class="green">QUOTATION<em>* item must be filled in</em></h1>
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
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">DELETE</a></b></div>';
		}else{
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">DELETE</a></b></div>';
		}
	}
}else{
	echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
}
?>
</fieldset>
*/
?>
<fieldset> 
<legend class='legend'>Action</legend>
<div style="margin-left:28px;"><a class="button" href="model/com/quotation_pdf.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/quotation_pdf_photo_list.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="model/com/quotation_pdf_photo_price.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo Price</b></a><a class="button" href="?act=com-modifyproforma&qid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add To Proforma</b></a></div>
</fieldset>
<fieldset> 
<legend class='legend'>Modify Quotation</legend>
<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr class="formtitle">
  	<td width="25%"><? $goodsForm->show('q_qid');?></td>
    <td width="25%"><? $goodsForm->show('q_send_to');?></td>
	<td width="25%"><? $goodsForm->show('q_attention');?></td>
    <td width="25%"><? $goodsForm->show('q_reference');?></td>
  </tr>
  <tr>
  	<td width="25%"><? $goodsForm->show('q_mark_date');?></td>
  	<td width="25%"><? $goodsForm->show('q_currency');?></td>
  	<td width="25%"><? $goodsForm->show('q_unit');?></td>
    <td width="25%"><? $goodsForm->show('q_discount');?></td>
  </tr>
  <tr>
  	<td width="25%" colspan="2"><? $goodsForm->show('q_remark');?></td>
	<td width="25%"><? $goodsForm->show('q_created_by');?></td>
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
    <td width="3%">&nbsp;</td>
	<td width="5%">&nbsp;</td>
  </tr>
  <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
  	<td class="dragHandle"></td>
    <td><? $goodsForm->show('q_pid');?></td>
    <td><? $goodsForm->show('q_p_description');?></td>
    <td><? $goodsForm->show('q_p_quantity');?></td>
    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price'); $goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>   
    <td id="sub">0</td>
    <td></td>
	<td><div id="his"></div></td>
<!--    <td><div id="clear"></div></td>-->
    <td><div id="del"></div></td>
  </tr>
<script>
	searchProduct(11, '');
</script>
<?
for($i = 0; $i < $q_item_num; $i++){
	if (is_file($pic_path_com . $q_item_rtn[$i]['photos']) == true) { 
		
		//壓縮圖片
		//$q_item_rtn[$i]['photos']是原來的， $small_photo 是縮小後的
		//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
		$small_photo = 's_' . $q_item_rtn[$i]['photos'];
		//縮小的圖片不存在才進行縮小操作
		if (!is_file($pic_path_small . $small_photo) == true) { 	
			makethumb($pic_path_com . $q_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
		}
		
		/*壓縮後就能直接顯示了，不用再改變width 和 height 了
		$arr = getimagesize($pic_path_com . $q_item_rtn[$i]['photos']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(80, 60, $pic_width, $pic_height);
		$photo_string = '<a href="/sys/'.$pic_path_com . $q_item_rtn[$i]['photos'].'" target="_blank" title="'.$q_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
		*/
		$photo_string = '<a href="/sys/'.$pic_path_com . $q_item_rtn[$i]['photos'].'" target="_blank" title="'.$q_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
	}else{ 
		$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
	}	
?>
  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
  	<td id="index" class="dragHandle"><?=$i+1?></td>
    <td><? $goodsForm->show('q_pid'.$i);?></td>
    <td><? $goodsForm->show('q_p_description'.$i);?></td>
    <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
    <td id="sub"><?=formatMoney($q_item_rtn[$i]['price']*(($q_item_rtn[$i]['quantity'] == 0 || $q_item_rtn[$i]['quantity'] == '')?1:$q_item_rtn[$i]['quantity']))?></td>
    <td><?=$photo_string?></td>
	<td><div id="his<?=$i?>"><img src="../../sys/images/history-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="History" /></div></td>
<!--    <td><div id="clear--><?//=$i?><!--"><img src="../../sys/images/clear.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Clear" /></div></td>-->
    <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
	<td><div id="del<?=$i?>"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div></td>
  </tr>
  
<script>
    //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
    searchProduct(11, <?=$i?>);
    //20130724 改为了blur出信息，所以q_pid的blur时间要unbind掉了
    $("#q_pid<?=$i?>").unbind();
</script>
<?	
}
?>  

<? //这里是为了多出一个空行，来可以方便输入，不用在enter旧的来新增一行，因为这样旧的内容会改变。。。 ?>
  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
  	<td class="dragHandle"></td>
    <td><? $goodsForm->show('q_pid'.$i);?></td>
    <td><? $goodsForm->show('q_p_description'.$i);?></td>
    <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
    <? /*<td><? $goodsForm->show('pi_p_remark');?></td>*/ ?>
    <td><? $goodsForm->show('q_p_price'.$i);$goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
    <td id="sub">0</td>
    <td>&nbsp;</td>
	<td><div id="his<?=$i?>"></div></td>
<!--    <td><div id="clear--><?//=$i?><!--"></div></td>-->
    <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
	<td><div id="del<?=$i?>"></div></td>
  </tr>
  <script>
      //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
      searchProduct(11, <?=$i?>);
  </script>


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
<?
$goodsForm->show('q_remarks');
?>
<div class="line"></div>
<?
$goodsForm->show('submitbtn');
?>
</fieldset>

<fieldset> 
<legend class='legend'>Action</legend>
<div style="margin-left:28px;"><a class="button" href="model/com/quotation_pdf.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/quotation_pdf_photo_list.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="model/com/quotation_pdf_photo_price.php?qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo Price</b></a><a class="button" href="?act=com-modifyproforma&qid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>Add To Proforma</b></a></div>
</fieldset>
<?
$goodsForm->end();

}
?>

<script>
$(function(){
	//load頁面就更新total值
	UpdateTotal();
	$(".template").hide();
	//***先加載當前屏幕的img，好像沒有效果。。。
	/*
	$("img").lazyload({
		placeholder : "/sys/images/grey.gif",
		effect      : "fadeIn"
	});
	*/
	//***	
	//table tr层表单可拖动, mod 20120921 显示可拖动的标志，按住标志就可拖动
	$('#tableDnD').tableDnD({
		/*
	    onDrop: function(table, row) {
			generateIndex();//更新item index
	    },
		*/		
        dragHandle: ".dragHandle"
    });
	/*
	$("#tableDnD tr[class=repeat][id!='']").hover(function() {
          $(this.cells[0]).addClass('showDragHandle');
    }, function() {
          $(this.cells[0]).removeClass('showDragHandle');
    });
	*/
	currency("q_");
})
</script>