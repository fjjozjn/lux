<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/
$goodsForm = new My_Forms();
$formItems = array(
		'd_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'nostar' => true),
		'fty_id' => array('type' => 'select', 'options' => get_pcid()),
		'staff' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
	//fb($_POST);die();
	//product item 的个数
	//'7'这个值是除item外的post的个数
	$item_num = (count($_POST) - 7) / 10;//！！'10'这个值随js里item的post个数改变而改变
			
	//$myerror->info($item_num);		
	//$myerror->info($_POST);
	
	$mypost = array();
	foreach($_POST as $v){
		$mypost[] = $v;	
	}
	//$myerror->info($mypost);
	
	$item = array();
	for($i = 0; $i < $item_num; $i++){		
		$item[$i]['box_num'] = $mypost[$i*10+4];
		$item[$i]['inner_box_num'] = $mypost[$i*10+5];
		$item[$i]['po_id'] = $mypost[$i*10+6];
		$item[$i]['p_id'] = $mypost[$i*10+7];
		$item[$i]['quantity'] = $mypost[$i*10+8];
		$item[$i]['weight'] = $mypost[$i*10+9];
		$item[$i]['size_l'] = $mypost[$i*10+10];
		$item[$i]['size_w'] = $mypost[$i*10+11];
		$item[$i]['size_h'] = $mypost[$i*10+12];
		$item[$i]['remark'] = $mypost[$i*10+13];		
	}
	
	//$myerror->info($item);
	//die();		
			
			
	//工厂自己的出货单号必须是唯一的
	$rs = $mysql->q('select id from delivery where d_id = ?', $_POST['d_id']);
	if(!$rs){
		$rs = $mysql->q('insert into delivery values (NULL, '.moreQm(7).')', $_POST['d_id'], dateMore(), $_POST['express_cost'], $_POST['express_id'], '', $_POST['staff'], $_SESSION['ftylogininfo']['aName']);
		if($rs){
			$success = true;
			
			//合计
			$total_all = 0;
			
			for($i = 0; $i < $item_num; $i++){	
				//找出purchase_item信息				
				$rtn_purchase = $mysql->qone('select p.customer, i.price, i.ccode from purchase p, purchase_item i where p.pcid = ? and i.pcid = ? and i.pid = ?', $item[$i]['po_id'], $item[$i]['po_id'], $item[$i]['p_id']);
	
				$rs_delivery = $mysql->q('insert into delivery_item values (NULL, '.moreQm(15).')', 
															 $_POST['d_id'],
															 $item[$i]['po_id'], 
															 $item[$i]['box_num'], 
															 $item[$i]['inner_box_num'], 
															 $rtn_purchase['customer'], 
															 $item[$i]['p_id'], 
															 $rtn_purchase['ccode'], 
															 $item[$i]['quantity'], 
															 $rtn_purchase['price'], 
															 $item[$i]['quantity']*$rtn_purchase['price'], 
															 $item[$i]['weight'], 
															 $item[$i]['size_l'], 
															 $item[$i]['size_w'], 
															 $item[$i]['size_h'], 
															 $item[$i]['remark']
															 );	
				if(!$rs_delivery){
					$success = false;	
				}	
				
				$total_all += $item[$i]['quantity']*$rtn_purchase['price'];
			}
			
			//更新total_all，保存一下这个值，省得以后要用还要去计算
			$total_all += $_POST['express_cost'];
			$mysql->q('update delivery set total_all = ? where d_id = ?', $total_all, $_POST['d_id']);	
			
			//更新 purchase 状态
			//$mysql->q('update purchase set istatus = ? where pcid = ?', '(S)', $_POST['fty_id']);
			//$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 5, $_SESSION['ftylogininfo']['aName']." ".$_POST['fty_id']." TO (S)", 15, "", "", 0);
			
			//快递费记录进overheads
			//2012.7.2 快递费现在是基于工厂的单子的，而不是基于每个purchase的，所以现在无法更新purchase的overheads
			//$mysql->q('insert into overheads (po_no, po_date, description, cost, cost_remark) values (?, ?, ?, ?, ?)', $_POST['fty_id'], dateMore(), 'Freight cost', $_POST['express_cost'], 'add by system');

			
			$myerror->ok('新增出货单 成功'.($success?'':'（新增item部分失败）').'!', 'searchdelivery&page=1');
		}else{
			$myerror->error('由于系统原因，新增出货单 失败(ERROR 1)', 'BACK');
		}
	}else{
		$myerror->warn('出货单 '. $_POST['d_id'].' 已存在，请不要重复添加', 'searchdelivery&page=1');
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



<?php
$goodsForm->begin();
?>
<table width="60%" align="center">
    <tr align="center">
        <td colspan="4" class='headertitle'>Add QC</td>
    </tr>
    <tr><td>&nbsp;</td></tr>
	<tr class="formtitle">
    	<td width="16%">QC NO. : <h6 class="required">*</h6></td>
      	<td width="34%"><? $goodsForm->show('d_id');?></td>
        <td width="15%">审核 ： </td>
        <td width="35%"><? $goodsForm->show('staff');?></td>
	</tr> 
</table>    
<div class="line"></div>
<div class="line"></div>
<table width="60%" align="center">       
	<tr class="formtitle">
    	<td width="16%">厂单号 : </td>
      	<td align="left"><? $goodsForm->show('fty_id');?></td></td>
        <!--td width="10%" align="left"><img title="add" style="opacity: 0.5;" onclick="addQc()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
        <td width="40%" align="left"><img title="delete" style="opacity: 0.5;" onclick="delQc()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td-->
	</tr>
</table>
<div class="line"></div>
<br />
<table id="delivery" width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' align="center">        
    <tr bgcolor='#EEEEEE'>  
        <th>款号</th>
        <th>图片</th>
        <th>总数（件）</th>
        <th>已检（件）</th>
        <th>问题</th>
        <th>程度：数量</th>
        <th>结果/行动</th>
        <th>工厂回应</th>
        <!--th colspan="2">操作</th-->
    </tr>
    <tbody id="tbody" class="qc" align="center"></tbody>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <!--td></td>
        <td></td-->
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <!--td></td>
        <td></td-->
    </tr>   
    <tr>
    	<td colspan="7"></td>
        <td><? $goodsForm->show('submitbtn');?></td>
        <!--td></td>
        <td></td-->
    </tr>  
</table>
<br />
<br />
<br />
<?
$goodsForm->end();
}
?>

<!--link href="/ui/swfupload/css/default.css" rel="stylesheet" type="text/css" />
<script src="/ui/swfupload/js/swfupload.js"></script>
<script src="/ui/swfupload/js/handlers.js"></script>
<script src="/ui/swfupload/js/fileprogress.js"></script-->

<link href="/ui/swfupload/css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/ui/swfupload/js/swfupload.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/handlers.js"></script>

<script>
$(function(){
	$("#fty_id").selectbox({onChange: changeQc});
})
</script>