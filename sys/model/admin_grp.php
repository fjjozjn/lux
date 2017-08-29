<?php
// print_r_pre($_SESSION);
//check IP

//check permission 
checkAdminPermission(PERM_VIEW_ADMINGRP);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
if (count($_POST)){
	$_SESSION['search_criteria'] = $_POST;
}
$form = new My_Forms();
$formItems = array(
		'admingrp_name' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['admingrp_name'], 
			),
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => '搜索', 
			'title' => '这个也可以提交'),
		/*
		'resetbutton' => array(
			'type' => 'button', 
			'value' => '清除', 
			'title' => '这个也可以提交'),	
		*/
		);
$form->init($formItems);
$form->begin();

// print_r_pre($_POST);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>

<table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">管理员群组管理</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td width="30%">群组名称 : </td>  
				<td width="30%"><?
				$form->show('admingrp_name');
				?></td>
				<td width="20%">&nbsp;</td>
				<td width="20%">&nbsp;</td>				
			</tr>	
			<tr>
				<td width="40%" colspan='4'>
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

	$rs = new RecordSetControl;
	$rs->record_per_page = ADMIN_ROW_PER_PAGE;
	$rs->addnew_link = "?act=admin_grp_form";
	$rs->display_new_button = true;
	$rs->sort_field = "AdminGrpID";
	$rs->sort_seq = "ASC";
	$rs->col_edit_col = "修改";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = ADMIN_ROW_PER_PAGE;
	if (set($_GET['page'])){
		$current_page = intval($_GET['page']);
		$start_row = (($current_page-1) * $rs->record_per_page);
	}
	
	$where_sql = "";
	
	if (strlen(@$_SESSION['search_criteria']['admingrp_name'])){
		$where_sql.= " AND AdminGrpName LIKE '%".$_SESSION['search_criteria']['admingrp_name']."%'";
	}
	
	$_SESSION['search_criteria']['page'] = $current_page;
	
	//get the row count for this seaching criteria
	//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', 'tw_admingrp',$where_sql);
	// echo $row_count;
	$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, 'tw_admingrp',$where_sql);
	// echo 'CALL backend_list('.$start_row.', '.$end_row.', "tw_admingrp", "'.$where_sql.'")';
	// echo $start_row.' VS '.$end_row;
	//$rs->col_width = "100";
	$rs->SetRecordCol("ID", "AdminGrpID");
	$rs->SetRecordCol("群组名称", "AdminGrpName");
	// $rs->SetRecordCol("COL 3", "AdminGrpDefaultPermCode");
	
	$sort = GENERAL_NO;
	$edit = GENERAL_YES;
	$rs->SetRecordCol("", "AdminGrpID", $sort, $edit,"?act=admin_grp_form","id");
	$rs->SetRSSorting('?act=admin_grp');
	
	/*
	$cur_page = 0;
	if (isset($_POST["page"])){
	$cur_page = $_POST["page"] - 1;
	}
	*/
	
	$rs->ShowRecordSet($info);
}
?>