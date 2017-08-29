<?php
// print_r_pre($_SESSION);

//check permission 
//die();
//checkAdminPermission(PERM_VIEW_ADMIN);

/**
 * 这里用 fty_lux_group 来记录用户所属的组群，如果用户属于同一组群，就能互相浏览与修改彼此的单
 */
 
//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}
 
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	//引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	
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
	$row_grp[] = array($v['FtyGrpName'], $v['FtyGrpID']);
}
*/
// print_r_pre($temp_grp);
$form = new My_Forms();
$formItems = array(
		'fty_login' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['fty_login'], 
			),			
		'fty_name' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['fty_name'], 
			),
			/*
		'fty_lux_group' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['fty_lux_group'], 
			),	
		'fty_grp' => array(
			'type' => 'select', 			
			'value' => @$_SESSION['search_criteria']['fty_grp'],
			'options' => $row_grp,
			),
			*/					
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => 'Search', 
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


// resetJSForm('text', 'fty_name');
// print_r_pre($row_grp);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">Factory Account</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td height="35">Factory Account : </td>  
				<td><?
				$form->show('fty_login');
				?></td>
				<td>Supplier : </td>
				<td><?
				$form->show('fty_name');
				?></td>
			</tr>
			<? /*	
			<tr>
				<td height="35">Group : </td>
				<td><?
				$form->show('fty_lux_group');
				?></td>			
				<td>&nbsp;</td>  
				<td>&nbsp;</td>
			</tr>
			*/ ?>				
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
	
	$rs = new RecordSetControl2;
	$rs->record_per_page = ADMIN_ROW_PER_PAGE;
	$rs->display_new_button = false;
	$rs->sort_field = "FtyGrpID";
	$rs->sort_seq = "ASC";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = $rs->record_per_page;
	if (set($_GET['page'])){
		$current_page = intval($_GET['page']);
		$start_row = (($current_page-1) * $rs->record_per_page);
	}
	
	$where_sql = "";
	if (strlen(@$_SESSION['search_criteria']['fty_login'])){
		$where_sql.= " AND a.FtyLogin LIKE '%".$_SESSION['search_criteria']['fty_login']."%'";
	}
	if (strlen(@$_SESSION['search_criteria']['fty_name'])){
		$where_sql.= " AND a.FtyName LIKE '%".$_SESSION['search_criteria']['fty_name']."%'";
	}
	/*	
	if (strlen(@$_SESSION['search_criteria']['fty_lux_group'])){
		$where_sql.= " AND a.FtyLuxGroup = '".$_SESSION['search_criteria']['fty_lux_group']."'";
	}
	if (strlen(@$_SESSION['search_criteria']['fty_grp'])){
		$where_sql.= " AND a.FtyGrpID = '".$_SESSION['search_criteria']['fty_grp']."'";
	}
	*/
	// echo $where_sql;
	$_SESSION['search_criteria']['page'] = $current_page;
	
	//$temp_table = ' tw_admin a INNER JOIN tw_admingrp g ON (a.FtyGrpID = g.FtyGrpID) ';
	$temp_table = ' fty_user ';
	
	//get the row count for this seaching criteria
	//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
			
	$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
	// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
	//$rs->col_width = "100";
	//$rs->SetRecordCol("ID", "FtyID");
	$rs->SetRecordCol("Factory Account", "FtyLogin");
	$rs->SetRecordCol("Supplier", "FtyName");
	$rs->SetRecordCol("Name", "FtyNameChi");
	//$rs->SetRecordCol("Email", "FtyEmail");
	//$rs->SetRecordCol("Group", "FtyLuxGroup");
	//$rs->SetRecordCol("管理群組", "FtyGrpName");
	// $rs->SetRecordCol("COL 3", "FtyGrpDefaultPermCode");
	
	$sort = GENERAL_NO;
	$edit = GENERAL_YES;
	$rs->SetRecordCol("MODIFY", "FtyID", $sort, $edit,"?act=fty_manageuser","id");
	$rs->SetRecordCol("DEL", "FtyID", $sort, $edit,"?act=fty_manageuser","delid");
	$rs->SetRSSorting('?act=fty_searchuser');
	
	/*
	$cur_page = 0;
	if (isset($_POST["page"])){
	$cur_page = $_POST["page"] - 1;
	}
	*/
	
	$rs->ShowRecordSet($info);
}
?>