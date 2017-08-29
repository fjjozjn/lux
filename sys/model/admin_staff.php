<?php
// print_r_pre($_SESSION);

//check permission 
die();
checkAdminPermission(PERM_VIEW_ADMIN);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{

// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
if (count($_POST)){
	$_SESSION['search_criteria'] = $_POST;
	$_GET['page'] = 1;
}

//get staff group information
$mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
$temp_grp = $mysql->fetch(0,1);	
foreach($temp_grp as $v){
	$row_grp[] = array($v['AdminGrpName'], $v['AdminGrpID']);
}
// print_r_pre($temp_grp);
$form = new My_Forms();
$formItems = array(
		'admin_name' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['admin_name'], 
			),
		'admin_login' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['admin_login'], 
			),			
		'admin_grp' => array(
			'type' => 'select', 			
			'value' => @$_SESSION['search_criteria']['admin_grp'],
			'options' => $row_grp,
			),					
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => '搜索', 
			'title' => '這個也可以提交'),	
		/*
		'resetbutton' => array(
			'type' => 'button', 
			'value' => '清除', 
			'title' => '這個也可以提交'),	
		*/
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
		<td class='headertitle' align="center">管理員帳號管理</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td height="35">登入帳號 : </td>  
				<td><?
				$form->show('admin_login');
				?></td>
				<td>管理員名稱 : </td>
				<td><?
				$form->show('admin_name');
				?></td>
			</tr>	
			<tr>
				<td height="35">管理員群組 : </td>
				<td><?
				$form->show('admin_grp');
				?></td>			
				<td>&nbsp;</td>  
				<td>&nbsp;</td>
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
	
	$rs = new RecordSetControl;
	$rs->record_per_page = ADMIN_ROW_PER_PAGE;
	$rs->addnew_link = "?act=admin_staff_form";
	$rs->display_new_button = true;
	$rs->sort_field = "AdminGrpID";
	$rs->sort_seq = "ASC";
	$rs->col_edit_col = "修改";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = $rs->record_per_page;
	if (set($_GET['page'])){
		$current_page = intval($_GET['page']);
		$start_row = (($current_page-1) * $rs->record_per_page);
	}
	
	$where_sql = "";
	
	if (strlen(@$_SESSION['search_criteria']['admin_name'])){
		$where_sql.= " AND a.AdminName LIKE '%".$_SESSION['search_criteria']['admin_name']."%'";
	}
	if (strlen(@$_SESSION['search_criteria']['admin_login'])){
		$where_sql.= " AND a.AdminLogin LIKE '%".$_SESSION['search_criteria']['admin_login']."%'";
	}
	if (strlen(@$_SESSION['search_criteria']['admin_grp'])){
		$where_sql.= " AND a.AdminGrpID = '".$_SESSION['search_criteria']['admin_grp']."'";
	}
	// echo $where_sql;
	$_SESSION['search_criteria']['page'] = $current_page;
	
	$temp_table = ' tw_admin a INNER JOIN tw_admingrp g ON (a.AdminGrpID = g.AdminGrpID) ';
	
	//get the row count for this seaching criteria
	//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
			
	$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
	// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
	//$rs->col_width = "100";
	$rs->SetRecordCol("ID", "AdminID");
	$rs->SetRecordCol("登入帳號", "AdminLogin");
	$rs->SetRecordCol("管理員名稱", "AdminName");
	$rs->SetRecordCol("管理群組", "AdminGrpName");
	// $rs->SetRecordCol("COL 3", "AdminGrpDefaultPermCode");
	
	$sort = GENERAL_NO;
	$edit = GENERAL_YES;
	$rs->SetRecordCol("", "AdminID", $sort, $edit,"?act=admin_staff_form","id");
	$rs->SetRSSorting('?act=admin_staff');
	
	/*
	$cur_page = 0;
	if (isset($_POST["page"])){
	$cur_page = $_POST["page"] - 1;
	}
	*/
	
	$rs->ShowRecordSet($info);
}
?>