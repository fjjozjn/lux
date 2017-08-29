<?php
// print_r_pre($_SESSION);

//check permission 
//die();
//checkAdminPermission(PERM_VIEW_ADMIN);

 
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	//引用特殊的recordset class 文件
	require_once(ROOT_DIR.'fty/in38/recordset.class3.php');
	
// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
if (count($_POST)){
	$_SESSION['search_criteria'] = $_POST;
	$_GET['page'] = 1;
}

//get staff group information
/*
$mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
$temp_grp = $mysql->fetch(0,1);	
foreach($temp_grp as $v){
	$row_grp[] = array($v['AdminGrpName'], $v['AdminGrpID']);
}
*/
// print_r_pre($temp_grp);
$form = new My_Forms();
$formItems = array(
		'company' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['company'], 
			),			
		'contact' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['contact'], 
			),
		'tel' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['tel'], 
			),	
		'address' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['address'], 
			),				
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => 'Search', 
			),	
		);
$form->init($formItems);
$form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($row_grp);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">客户资料</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td height="35">公司 : </td>  
				<td><?
				$form->show('company');
				?></td>
				<td>联络人 : </td>
				<td><?
				$form->show('contact');
				?></td>
			</tr>	
			<tr>
				<td height="35">电话 : </td>
				<td><?
				$form->show('tel');
				?></td>			
				<td height="35">地址 : </td>
				<td><?
				$form->show('address');
				?></td>
			</tr>				
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');
				// $form->show('resetbutton');
				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table><br />
<?
	$form->end();
	
	//如果有合法的提交，則 getAnyPost = true。
	//如果不是翻頁而是普通的GET，則清除之前的Session，以顯示一個空白的表單
	$getAnyPost = false;
	if ($form->check()){
		$getAnyPost = true;
	}elseif(!isset($_GET['page'])){
		unset($_SESSION['search_criteria']);
	}
	
	if($myerror->getAny()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	
	$rs = new RecordSetControl3;
	$rs->record_per_page = ADMIN_ROW_PER_PAGE;
	$rs->display_new_button = false;
	$rs->sort_field = "id";
	$rs->sort_seq = "DESC";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = $rs->record_per_page;
	if (set($_GET['page'])){
		$current_page = intval($_GET['page']);
		$start_row = (($current_page-1) * $rs->record_per_page);
	}
	
	$where_sql = "";
	if (strlen(@$_SESSION['search_criteria']['company'])){
		$where_sql.= " AND company LIKE '%".$_SESSION['search_criteria']['company']."%'";
	}
	if (strlen(@$_SESSION['search_criteria']['contact'])){
		$where_sql.= " AND contact LIKE '%".$_SESSION['search_criteria']['contact']."%'";
	}
	if (strlen(@$_SESSION['search_criteria']['tel'])){
		$where_sql.= " AND tel = '".$_SESSION['search_criteria']['tel']."'";
	}
	if (strlen(@$_SESSION['search_criteria']['address'])){
		$where_sql.= " AND address = '".$_SESSION['search_criteria']['address']."'";
	}
	
	
	if(!isFtyAdmin()){
		$where_sql .= " AND (created_by = '".$_SESSION['ftylogininfo']['aName']."' or created_by = 'all')";
	}
	
	// echo $where_sql;
	$_SESSION['search_criteria']['page'] = $current_page;
	
	$temp_table = ' fty_client ';
				
	$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
	// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
	//$rs->col_width = "100";
	$rs->SetRecordCol("公司", "company");
	$rs->SetRecordCol("联络人", "contact");
	$rs->SetRecordCol("电话", "tel");
	$rs->SetRecordCol("地址", "address");
	
	$sort = GENERAL_NO;
	$edit = GENERAL_YES;
	$rs->SetRecordCol("修改", "id", $sort, $edit,"?act=manageclient","id");
	$rs->SetRecordCol("删除", "id", $sort, $edit,"?act=manageclient","delid");
	$rs->SetRSSorting('?act=searchclient');
	
	/*
	$cur_page = 0;
	if (isset($_POST["page"])){
	$cur_page = $_POST["page"] - 1;
	}
	*/
	
	$rs->ShowRecordSet($info);
}
?>