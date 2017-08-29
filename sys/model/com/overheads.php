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
}

if( !isset($_POST['s_sign']) || $_POST['s_sign'] != 1){
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		$rtn = $mysql->qone('select cost from overheads where id = ?', $_GET['delid']);
		$rs = $mysql->q('delete from overheads WHERE id = ?', $_GET['delid']);
		if($rs){
			//20121017更新 settlement 的 Total 和 Outstanding Value
			$mysql->q('update settlement set s_total = s_total - ?, outstanding_value = outstanding_value - ?', $rtn['cost'], $rtn['cost']);
			$myerror->ok('Success!', 'com-overheads&page=1');	
		}else{
			$myerror->error('Failure!', 'com-overheads&page=1');	
		}
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from overheads where id = ?', $_GET['modid']);
}

$goodsForm = new My_Forms();
$formItems = array(
		
		//'po_no' => array('title' => 'PO#', 'type' => 'select', 'options' => $pcid, 'required' => 1, 'value' => isset($mod_result['po_no'])?$mod_result['po_no']:''),
		'description' => array('title' => 'Description', 'type' => 'select', 'options' => $description, 'required' => 1, 'value' => isset($mod_result['description'])?$mod_result['description']:'', 'info' => '(The ex-factory cost will be counted into the settlement)'),
		'po_date' => array('title' => 'DATE', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['po_date'])?date('Y-m-d', strtotime($mod_result['po_date'])):''),
		'cost' => array('title' => 'Cost(RMB)', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['cost'])?$mod_result['cost']:''),
		'cost_remark' => array('title' => 'Cost Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:''),
		'ref' => array('title' => 'REF#', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['ref'])?$mod_result['ref']:''),
		'other' => array('title' => 'Other', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['other'])?$mod_result['other']:''),
		
		//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
		's_sign' => array('type' => 'hidden', 'value' => 1),
		
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
		'search_description' => array(
			'type' => 'select',
			'options' => $description, 
			'value' => @$_SESSION['search_criteria']['search_description'], 
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
		'search_ref' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_ref'], 	
			),
			/*							
		'search_s_sign' => array(
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

if(isset($_POST['s_sign']) && $_POST['s_sign'] == 1){
	if(!$myerror->getAny() && $goodsForm->check()){
		$po_no = $_POST['po_no'];
		$description = $_POST['description'];
		$po_date = $_POST['po_date'].' '.date('H:i:s');
		$cost = $_POST['cost'];
		$cost_remark = $_POST['cost_remark'];
		$ref = $_POST['ref'];
		$other = $_POST['other'];
		
		if( isset($_GET['modid']) && $_GET['modid'] != ''){
			$result = $mysql->q('update overheads set po_no = ?, description = ?, po_date = ?, cost = ?, cost_remark = ?, ref = ?, other = ? where id = ?', $po_no, $description, $po_date, $cost, $cost_remark, $ref, $other, $_GET['modid']);
			if($result){
				//这里还要考虑到description可能改变，太麻烦了。。。
				//20130409 之前update语句忘了加where条件了。。。而且所有状态的settlement只要po_no是这个，都改
				if($mod_result['description'] == 'Ex-factory' && $description == 'Ex-factory'){
				//20121017更新 settlement 的 Total 和 Outstanding Value
					$mysql->q('update settlement set s_total = s_total - ? + ?, outstanding_value = outstanding_value - ? + ? where po_no = ?', $mod_result['cost'], $cost, $mod_result['cost'], $cost, $po_no);
				}elseif($mod_result['description'] == 'Ex-factory' && $description != 'Ex-factory'){
					$mysql->q('update settlement set s_total = s_total - ?, outstanding_value = outstanding_value - ? where po_no = ?', $mod_result['cost'], $mod_result['cost'], $po_no);
				}elseif($mod_result['description'] != 'Ex-factory' && $description == 'Ex-factory'){
					$mysql->q('update settlement set s_total = s_total + ?, outstanding_value = outstanding_value + ? where po_no = ?', $cost, $cost, $po_no);
				}
				$myerror->ok('Success!', 'com-overheads&page=1');	
			}else{
				$myerror->error('Failure!', 'com-overheads&page=1');	
			}
		}else{
			//20121018 如果存在settlement，才能添加此po_no的overheads
			//担心用户不是先有settlement才有overheads，所以还是去掉了
			//if($mysql->qone('select id from settlement where po_no = ?', $po_no)){
				$result = $mysql->q('insert into overheads (po_no, description, po_date, cost, cost_remark, ref, other) values ('.moreQm(7).')', $po_no, $description, $po_date, $cost, $cost_remark, $ref, $other);
				if($result){
					//20121017更新 settlement 的 Total 和 Outstanding Value
					//20130409 之前update语句忘了加where条件了。。。
					$mysql->q('update settlement set s_total = s_total + ?, outstanding_value = outstanding_value + ? where po_no = ?', $cost, $cost, $po_no);
					$myerror->ok('Success!', 'com-overheads&page=1');		
				}else{
					$myerror->error('Failure!', 'com-overheads&page=1');
				}
			//}else{
				//$myerror->warn('请先添加settlement!', 'com-overheads&page=1');
			//}
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
<h1 class="green">Overheads<em>* item must be filled in</em></h1>
<?
$form->begin();
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>PO# : </td>
				<td><?
				$form->show('search_po_no');
				//$form->show('search_s_sign');
				?></td>	
            
				<td>Description : </td>
				<td><?
				$form->show('search_description');
				?></td>
			</tr>
			<tr>
				<td>Start Date : </td>
				<td><?
				$form->show('search_start_date');
				?></td>		
                
                <td>End Date : </td>
				<td><?
				$form->show('search_end_date');
				?></td>
			</tr>
			<tr>
				<td>Ref# : </td>
				<td><?
				$form->show('search_ref');
				?></td>	
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
//if(isset($_POST['search_s_sign']) && $_POST['search_s_sign'] == 1){
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
		$rs->addnew_link = "?act=com-overheads";
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
		if (strlen(@$_SESSION['search_criteria']['search_description'])){
			$where_sql.= " AND description Like '%".$_SESSION['search_criteria']['search_description'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
			if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
				$where_sql.= " AND po_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND po_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
			$where_sql.= " AND po_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
		}		
		if (strlen(@$_SESSION['search_criteria']['search_ref'])){
			$where_sql.= " AND ref Like '%".$_SESSION['search_criteria']['search_ref'].'%\'';
		}
		
		//普通用户只能搜索到自己开的单
		if (!isSysAdmin()){
			$where_sql .= " AND po_no in (select pcid from purchase where created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."')))";
		}
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY id DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
	
		$temp_table = ' overheads';
		$list_field = ' SQL_CALC_FOUND_ROWS * ';
	
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
		//$rs->col_width = "100";
		$rs->SetRecordCol("DATE", "po_date");		
		$rs->SetRecordCol("PO#", "po_no");
		$rs->SetRecordCol("REF#", "ref");
		$rs->SetRecordCol("Description", "description");
		$rs->SetRecordCol("Cost(RMB)", "cost");
		$rs->SetRecordCol("Cost Remark", "cost_remark");
		$rs->SetRecordCol("Other", "other");
	
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-overheads","modid");
		$rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-overheads","delid");
		$rs->SetRSSorting('?act=com-overheads');
	
		$rs->ShowRecordSet($info);
	
	}
//}

$form->end();	
?>

<br />
<br />
<fieldset class="center2col" style="width:80%">
<legend class='legend'><?=isset($_GET['modid'])?'Modify':'Add' ?></legend>

<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr valign="top">
  	<td width="25%"><? $goodsForm->show('po_date');?></td>
    <td width="25%"><? $goodsForm->show('po_no');?></td>
  	<td width="25%"><? $goodsForm->show('cost');?></td>
    <td width="25%"><? $goodsForm->show('cost_remark');?></td>  
  </tr> 
  <tr valign="top">
	<td width="25%"><? $goodsForm->show('ref');?></td>
	<td width="25%"><? $goodsForm->show('description');?></td>  
    <td width="25%"><? $goodsForm->show('other');?></td>
	<td width="25%"><? $goodsForm->show('s_sign');?></td>
  </tr> 
</table>
<div class="line"></div>
<?
$goodsForm->show('submitbtn');
?>

</fieldset>
<br />
<?
$goodsForm->end();
}
?>