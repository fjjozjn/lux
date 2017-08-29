<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['po_no'])){
	$_SESSION['search_criteria']['search_po_no'] = $_GET['po_no'];

    //20131203 1、Total 和 Outstanding Value 改为只读；2、po转到settlement，自动填上这两栏
    $ex_factory_cost = 0;
    $rtn_item = $mysql->q('select cost from overheads where po_no = ? and description = ?', $_GET['po_no'], 'Ex-factory');
    if($rtn_item){
        $rtn = $mysql->fetch();
        foreach($rtn as $v){
            $ex_factory_cost += $v['cost'];
        }
    }

    $total = 0;
    $outstanding_value = 0;
    $rtn_items = $mysql->q('select price, quantity from purchase_item where pcid = ?', $_GET['po_no']);
    if($rtn_items){
        $rtn = $mysql->fetch();
        foreach($rtn as $v){
            $total += $v['price'] * $v['quantity'];
        }

        $rtn = $mysql->q('select amount from settlement where po_no = ?', $_GET['po_no']);
        if($rtn){
            $rtn = $mysql->fetch();
            foreach($rtn as $v){
                $outstanding_value += $v['amount'];
            }
        }
        //20121017 加number_format 不知道为什么这里又出现了科学计算法的多位显示，为什么有多位，我也不知道
        //echo number_format(($total + $ex_factory_cost), 2, '.', '') . '|' . number_format(($total + $ex_factory_cost - $outstanding_value), 2, '.', '');

        $po_to_st_s_total = number_format(($total + $ex_factory_cost), 2, '.', '');
        $po_to_st_outstanding_value = number_format(($total + $ex_factory_cost - $outstanding_value), 2, '.', '');

        //echo '<script>alert("test");$("#s_total").val("'.number_format(($total + $ex_factory_cost), 2, '.', '').'");$("#outstanding_value").val("'.number_format(($total + $ex_factory_cost - $outstanding_value), 2, '.', '').'");</script>';
    }
}

//提示状态变化的信息
$add_info = '';

if( !isset($_POST['st_sign']) || $_POST['st_sign'] != 1){
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		$rtn = $mysql->qone('select po_no, st_status from settlement where id = ?', $_GET['delid']);
		if($rtn['st_status'] == 'Balance'){
			  $result = $mysql->qone('select istatus from purchase where pcid = ?', $rtn['po_no']);
			  if($result['istatus'] == '(C)'){
				  $add_info = $rtn['po_no'].' status change from ( C ) to ( S ) !';
				  $mysql->q('update purchase set istatus = ? where pcid = ?', '(S)', $rtn['po_no']);
				  $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['po_no']." (C) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
			  }elseif($result['istatus'] == '(P)'){
				  $add_info = $rtn['po_no'].' status change from ( P ) to ( I ) !';
				  $mysql->q('update purchase set istatus = ? where pcid = ?', '(I)', $rtn['po_no']);
				  $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['po_no']." (P) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
			  }	
		}
		
		$rs = $mysql->q('delete from settlement WHERE id = ?', $_GET['delid']);
		if($rs){
			$myerror->ok('Success! '.$add_info, 'com-settlement&page=1');	
		}else{
			$myerror->error('Failure!', 'com-settlement&page=1');	
		}
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from settlement where id = ?', $_GET['modid']);
	//20130409 去掉已完成settlement不能修改的设定
	/*
	if(!$mysql->qone('select pcid from purchase where (istatus = ? OR istatus = ?) AND pcid = ?', '(I)', '(S)', $mod_result['po_no'])){
		$myerror->error('This can not be modified !', 'com-settlement&page=1');
	}
	*/
}

$goodsForm = new My_Forms();
$formItems = array(
		
		//'po_no' => array('title' => 'PO#', 'type' => 'select', 'options' => $pcid, 'required' => 1, 'value' => isset($mod_result['po_no'])?$mod_result['po_no']:''),
		's_total' => array('title' => 'Total', 'type' => 'text'/*, 'restrict' => 'number'*/, 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['s_total'])?$mod_result['s_total']:'', 'readonly' => 'readonly'),
		'outstanding_value' => array('title' => 'Outstanding Value', 'type' => 'text'/*, 'restrict' => 'number'*/, 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['outstanding_value'])?$mod_result['outstanding_value']:'', 'readonly' => 'readonly'),
		'amount' => array('title' => 'Amount(RMB)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['amount'])?$mod_result['amount']:'', 'addon' => 'onblur="sttlement_amount_blur(this)" onfocus="sttlement_amount_focus(this)"'),
		'st_date' => array('title' => 'DATE', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['st_date'])?date('Y-m-d', strtotime($mod_result['st_date'])):''),
		'method' => array('title' => 'Method', 'type' => 'select', 'options' => $method, 'required' => 1, 'value' => isset($mod_result['method'])?$mod_result['method']:''),
		'bank_ref' => array('title' => 'Bank Ref', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['bank_ref'])?$mod_result['bank_ref']:''),
		'st_status' => array('title' => 'Deposit/Balance', 'type' => 'select', 'options' => $settlement, 'required' => 1, 'value' => isset($mod_result['st_status'])?$mod_result['st_status']:''),
		'bank_charge' => array('title' => 'Bank charge (HKD)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['bank_charge'])?$mod_result['bank_charge']:''),		
		'remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
		
		//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
		'st_sign' => array('type' => 'hidden', 'value' => 1),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);

//20130315 modify的时候不能显示出已经balance的po，也就无法修改，所以这里在修改的时候不用选择框了，改为文本框
if(isset($_GET['modid'])){
	$formItems['po_no'] = array('title' => 'PO#', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['po_no'])?$mod_result['po_no']:'');
}else{
	$formItems['po_no'] = array('title' => 'PO#', 'type' => 'select', 'options' => get_pcid(), 'required' => 1, 'value' => isset($mod_result['po_no'])?$mod_result['po_no']:'');
}		
		
$goodsForm->init($formItems);


$form = new My_Forms();
$formItems = array(

		'search_po_no' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_po_no'],
			),	
		'search_bank_ref' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_bank_ref'], 
			),	
		'search_start_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['search_start_date'], 
			),	
		'search_end_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['search_end_date'], 
			),	
		'search_st_status' => array(
			'type' => 'select', 
			'options' => $settlement,
			'value' => @$_SESSION['search_criteria']['search_st_status'], 	
			),
		'search_method' => array(
			'type' => 'select', 
			'options' => $method,
			'value' => @$_SESSION['search_criteria']['search_method'], 
			),	
		'search_remark' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_remark'], 
			),			
			/*							
		'search_st_status' => array(
			'type' => 'hidden', 
			'value' => 1
			),
			*/
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => 'Search', 
			)			
		);
$form->init($formItems);

if(isset($_POST['st_sign']) && $_POST['st_sign'] == 1){
	if(!$myerror->getAny() && $goodsForm->check()){
		$po_no = $_POST['po_no'];
		$amount = $_POST['amount'];
		$st_date = $_POST['st_date'].' '.date('H:i:s');
		$method = $_POST['method'];
		$bank_ref = $_POST['bank_ref'];
		$st_status = $_POST['st_status'];
		$remark = $_POST['remark']; 
		$bank_charge = $_POST['bank_charge'];
		//mod 20130105 不管表单传来的 s_total 和 outstanding_value 是什么，插入数据库的都是按照 ajax/choose_settlement_pcid.php 中的算法重新算一遍，这样就不会出现 outstanding_value 的值在更新数据后不会相应更新的情况了
		//mod 20130410 又开始使用post来的 s_total 和 outstanding_value
		$s_total = $_POST['s_total'];
		$outstanding_value = $_POST['outstanding_value'];
		
		/*
		$ex_factory_cost = 0;
		$rtn_item = $mysql->q('select cost from overheads where po_no = ? and description = ?', $po_no, 'Ex-factory');
		if($rtn_item){
			$rtn = $mysql->fetch();
			foreach($rtn as $v){
				$ex_factory_cost += $v['cost'];
			}
		}
		
		$total = 0;
		$outstanding_value = 0;
		$rtn_items = $mysql->q('select price, quantity from purchase_item where pcid = ?', $po_no);
		if($rtn_items){
			$rtn = $mysql->fetch();
			foreach($rtn as $v){
				$total += $v['price'] * $v['quantity'];	
			}
			
			$rtn = $mysql->q('select amount from settlement where po_no = ?', $po_no);
			if($rtn){
				$rtn = $mysql->fetch();
				foreach($rtn as $v){
					$outstanding_value += $v['amount'];	
				}
			}
		}
		$s_total = number_format(($total + $ex_factory_cost), 2, '.', '');
		$outstanding_value = number_format(($total + $ex_factory_cost - $outstanding_value), 2, '.', '');
		*/
		
		//mod 20130105 暂定mod情况下，不修改 s_total 和 outstanding_value 的值，这两个值会被以后的数据影响，所以，如果在之后修改，则旧的值会被更新
		//mod 20130410 因为在overheads里也对settlement 的值有修改，所以，现在提交这两个值也行了
		if( isset($_GET['modid']) && $_GET['modid'] != ''){
			//状态没有被修改，不会对purchase的status产生影响
			if($mod_result['st_status'] == $st_status){
				$result = $mysql->q('update settlement set po_no = ?, amount = ?, st_date = ?, method = ?, bank_ref = ?, st_status = ?, remark = ?, bank_charge = ?, s_total = ?, outstanding_value = ? where id = ?', $po_no, $amount, $st_date, $method, $bank_ref, $st_status, $remark, $bank_charge, $s_total, $outstanding_value, $_GET['modid']);
				if($result){
					$myerror->ok('Success!', 'com-settlement&page=1');	
				}else{
					$myerror->error('Failure!', 'com-settlement&page=1');	
				}
			}
			//状态被修改了
			elseif($mod_result['st_status'] != $st_status){	
				//状态由 Deposit 修改为 Balance
				if($st_status == 'Balance'){
					//如果同一个$po_no已经存在Balance就不能在插入Balance项了
					if(!$mysql->qone('select id from settlement where po_no = ? where st_status = ?', $po_no, 'Balance')){
						$result = $mysql->q('update settlement set po_no = ?, amount = ?, st_date = ?, method = ?, bank_ref = ?, st_status = ?, remark = ?, bank_charge = ?, s_total = ?, outstanding_value = ? where id = ?', $po_no, $amount, $st_date, $method, $bank_ref, $st_status, $remark, $bank_charge, $s_total, $outstanding_value, $_GET['modid']);
						if($result){			
							$rtn = $mysql->qone('select istatus from purchase where pcid = ?', $po_no);
							if($rtn['istatus'] == '(I)'){
								$add_info = '----' . $po_no . ' status change from ( I ) to ( P ) !';
								$mysql->q('update purchase set istatus = ? where pcid = ?', '(P)', $po_no);
								$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (I) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
							}elseif($rtn['istatus'] == '(S)'){
								$add_info = '----' . $po_no . ' status change from ( S ) to ( C ) !';
								$mysql->q('update purchase set istatus = ? where pcid = ?', '(C)', $po_no);
								$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (S) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
							}//因为前面判断没有Balance，所以不会有(C)
							$myerror->ok('Success! '.$add_info, 'com-settlement&page=1');	
						}else{
							$myerror->error('Failure!', 'com-settlement&page=1');	
						}
					}else{
						$myerror->warn('此Purchase状态已是Balance，不允许再修改为Balance项', 'com-settlement&page=1');
					}
				}
				//状态由 Balance 修改为 Deposit
				elseif($st_status == 'Deposit'){
					$result = $mysql->q('update settlement set po_no = ?, amount = ?, st_date = ?, method = ?, bank_ref = ?, st_status = ?, remark = ?, bank_charge = ?, s_total = ?, outstanding_value = ? where id = ?', $po_no, $amount, $st_date, $method, $bank_ref, $st_status, $remark, $bank_charge, $s_total, $outstanding_value, $_GET['modid']);
					if($result){
						$rtn = $mysql->qone('select istatus from purchase where pcid = ?', $po_no);
						if($rtn['istatus'] == '(C)'){
							$add_info = '----' . $po_no . ' status change from ( C ) to ( S ) !';
							$mysql->q('update purchase set istatus = ? where pcid = ?', '(S)', $po_no);
							$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (C) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
						}elseif($rtn['istatus'] == '(P)'){
							$add_info = '----' . $po_no . ' status change from ( P ) to ( I ) !';
							$mysql->q('update purchase set istatus = ? where pcid = ?', '(I)', $po_no);
							$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (P) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
						}//因为是由Balance转为Deposit，所以不会有(I)
						$myerror->ok('Success! '.$add_info, 'com-settlement&page=1');	
					}else{
						$myerror->error('Failure!', 'com-settlement&page=1');	
					}
				}
			}
		}else{
			//20121018 修改amount时，同时修改 total 和 outstanding
			//暂缺
			//20130410 amount 框 blur后，自动修改total 和 outstanding
			
			//插入状态为 Balance
			if($st_status == 'Balance'){
				//如果同一个$po_no已经存在Balance就不能在插入Balance项了
				if(!$mysql->qone('select id from settlement where po_no = ? and st_status = ?', $po_no, 'Balance')){
					$result = $mysql->q('insert into settlement (po_no, amount, st_date, method, bank_ref, st_status, remark, bank_charge, s_total, outstanding_value) values ('.moreQm(10).')', $po_no, $amount, $st_date, $method, $bank_ref, $st_status, $remark, $bank_charge, $s_total, $outstanding_value);
					if($result){
						$rtn = $mysql->qone('select istatus from purchase where pcid = ?', $po_no);
						if($rtn['istatus'] == '(I)'){
							$add_info = '----' . $po_no . ' status change from ( I ) to ( P ) !';
							$mysql->q('update purchase set istatus = ? where pcid = ?', '(P)', $po_no);
							$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (I) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
						}elseif($rtn['istatus'] == '(S)'){
							$add_info = '----' . $po_no . ' status change from ( S ) to ( C ) !';
							$mysql->q('update purchase set istatus = ? where pcid = ?', '(C)', $po_no);
							$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (S) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
						}elseif($rtn['istatus'] == ''){//为空是因为5月之前还没有加status变化的内容，所以5月之前的单status字段是空的
							$add_info = '----' . $po_no . ' status change to ( P ) !';
							$mysql->q('update purchase set istatus = ? where pcid = ?', '(P)', $po_no);
							$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
						}//因为前面判断没有Balance，所以不会有(C)
						$myerror->ok('Success! '.$add_info, 'com-settlement&page=1');		
					}else{
						$myerror->error('Failure!', 'com-settlement&page=1');
					}
				}else{
					$myerror->warn('此Purchase状态已是Balance，不允许再插入Balance项', 'com-settlement&page=1');
				}
			}
			//如果插入状态为 Deposit  一般不用改状态，但是原来status为空则要加上(I)
			elseif($st_status == 'Deposit'){
				$result = $mysql->q('insert into settlement (po_no, amount, st_date, method, bank_ref, st_status, remark, bank_charge, s_total, outstanding_value) values ('.moreQm(10).')', $po_no, $amount, $st_date, $method, $bank_ref, $st_status, $remark, $bank_charge, $s_total, $outstanding_value);
				if($result){
					$rtn = $mysql->qone('select istatus from purchase where pcid = ?', $po_no);
					if($rtn['istatus'] == ''){
						//状态为空则改为(I)且不提示修改的信息
						$mysql->q('update purchase set istatus = ? where pcid = ?', '(I)', $po_no);
						$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$po_no." (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
					}					
					$myerror->ok('Success!', 'com-settlement&page=1');
				}else{
					$myerror->error('Failure!', 'com-settlement&page=1');
				}
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
<h1 class="green">Settlement<em>* item must be filled in</em></h1>

<?php
$form->begin();
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="right">PO# : </td>
				<td align="left"><? $form->show('search_po_no');//$form->show('search_st_status'); ?></td>
				<td align="right">Bank Ref : </td>
				<td align="left"><? $form->show('search_bank_ref'); ?></td>
			</tr>
			<tr>
				<td align="right">Start Date : </td>
				<td align="left"><? $form->show('search_start_date'); ?></td>
                <td align="right">End Date : </td>
				<td align="left"><? $form->show('search_end_date'); ?></td>
			</tr>
			<tr>
				<td align="right">Deposit/Balance : </td>
				<td align="left"><? $form->show('search_st_status'); ?></td>
				<td align="right">Method : </td>
				<td align="left"><? $form->show('search_method'); ?></td>
			</tr>	
			<tr>
				<td align="right">Remark : </td>
				<td align="left"><? $form->show('search_remark'); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	                
			</tr>	            		
            <tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table>    
<?
//if(isset($_POST['search_st_status']) && $_POST['search_st_status'] == 1){
	// 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
		$_GET['page'] = 1;
	}
	
	//如果有合法的提交，则 getAnyPost = true。
	//如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
	$getAnyPost = false;
	
	if($form->check()){
		$getAnyPost = true;
	}elseif(!isset($_GET['page'])){
		unset($_SESSION['search_criteria']);
	}	
	
	if($myerror->getAny()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}	
		
	if ($getAnyPost || isset($_GET['page'])){
		
		$rs = new RecordSetControl2;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=com-settlement";
		$rs->display_new_button = false;
		$rs->sort_field = "po_no";
		$rs->sort_seq = "DESC";
	
		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}
	
		$where_sql = "";
	
		if (strlen(@$_SESSION['search_criteria']['search_po_no'])){
			$where_sql.= " AND po_no Like '%".$_SESSION['search_criteria']['search_po_no'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_bank_ref'])){
			$where_sql.= " AND bank_ref Like '%".$_SESSION['search_criteria']['search_bank_ref'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
			if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
				$where_sql.= " AND st_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND st_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
			$where_sql.= " AND st_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
		}		
		if (strlen(@$_SESSION['search_criteria']['search_st_status'])){
			$where_sql.= " AND st_status Like '%".$_SESSION['search_criteria']['search_st_status'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_method'])){
			$where_sql.= " AND method Like '%".$_SESSION['search_criteria']['search_method'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_remark'])){
			$where_sql.= " AND remark Like '%".$_SESSION['search_criteria']['search_remark'].'%\'';
		}
		
		//普通用户只能搜索到自己开的单
		if (!isSysAdmin()){
			$where_sql .= " AND po_no in (select pcid from purchase where created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."')))";
		}		
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY id DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
	
		$temp_table = ' settlement';
		$list_field = ' SQL_CALC_FOUND_ROWS * ';
	
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
		//$rs->col_width = "100";
		$rs->SetRecordCol("DATE", "st_date");
		$rs->SetRecordCol("Deposit/Balance", "st_status");
		$rs->SetRecordCol("PO#", "po_no");		
		$rs->SetRecordCol("Amount(RMB)", "amount");
		$rs->SetRecordCol("Total", "s_total");
		$rs->SetRecordCol("Outstanding Value", "outstanding_value");
		$rs->SetRecordCol("Method", "method");
		$rs->SetRecordCol("Bank Ref", "bank_ref");
		$rs->SetRecordCol("Bank charge (HKD)", "bank_charge");
		$rs->SetRecordCol("Remark", "remark");
	
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-settlement","modid");
		$rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-settlement","delid");
		$rs->SetRSSorting('?act=com-settlement');
	
		$rs->ShowRecordSet($info);
	
	}
//}

$form->end();	
?>



<br />
<br />
<fieldset class="center2col" style="width:80%">
<legend class='legend'><?=isset($_GET['modid'])?'Modify':'Add' ?></legend>
<?
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr>
  	<td width="25%"><? $goodsForm->show('po_no');?></td>
    <td width="25%"><? $goodsForm->show('s_total');?></td>
    <td width="25%"><? $goodsForm->show('outstanding_value');?></td>
  	<td width="25%"><? $goodsForm->show('st_status');?></td>
  </tr> 
  <tr>
	<td width="25%"><? $goodsForm->show('st_date');?></td>
	<td width="25%"><? $goodsForm->show('amount');?></td>   
  	<td width="25%"><? $goodsForm->show('method');?></td>
    <td width="25%"><? $goodsForm->show('bank_ref');?></td>
  </tr> 
  <tr>
	<td width="25%"><? $goodsForm->show('bank_charge');?></td>
    <td width="25%"><? $goodsForm->show('remark');$goodsForm->show('st_sign');?></td>  
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
	pcidChange();

    //20131203
    var local_url = location.href;
    if(local_url.indexOf('&po_no=') > 0){
        var selectText = $("#po_no_container li").parent().parent().prev().val();

        var qs = 'ajax=settlement&act=ajax-choose_settlement_pcid&value='+selectText;
        $.ajax({
            type: "GET",
            url: "index.php",
            data: qs,
            cache: false,
            dataType: "html",
            error: function(){
                alert('system error!');
            },
            success: function(data){
                if(data.indexOf('no-') < 0){
                    var data_array = data.split("|");
                    $("#s_total").val(data_array[0]);
                    $("#outstanding_value").val(data_array[1]);
                }else{
                    alert('PO#不存在或其他原因，自动计算Total与Outstanding Value出错');
                }
            }
        })
    }
});
</script>