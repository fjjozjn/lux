<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{

// 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
if (count($_POST)){
	$_SESSION['search_criteria'] = $_POST;
	$_GET['page'] = 1;
}

//get staff group information
// $mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
// $temp_grp = $mysql->fetch(0,1);	
// for($i = 0 ; $i < count($temp_grp); $i++){
	// $temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
	// $row_grp[] = $temp;
// }
// print_r_pre($temp_grp);
$form = new My_Forms();
$formItems = array(
		// 'game_name' => array(
			// 'type' => 'text', 
			// 'value' => @$_SESSION['search_criteria']['game_name'], 
			// ),
		'game_login' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['game_login'], 
			),	
		'member_login' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['member_login'], 
			),	
		'game_start_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['game_start_date'], 
			),	
		'game_end_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['game_end_date'], 
			),		
		'game_regip' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['game_regip'], 
			),					
		'game_name' => array(
			'type' => 'select', 			
			'value' => @$_SESSION['search_criteria']['game_name'],
			'required' => GENERAL_YES,
			'nostar' => GENERAL_YES,
			'options' => getGameListByUserPerm(),
			),					
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => '搜索', 
			'title' => '这个也可以提交'),	
		'resetbutton' => array(
			'type' => 'button', 
			'value' => '清除', 
			'title' => '这个也可以提交'),			
		);
$form->init($formItems);
$form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">查询游戏帐号</td>
	</tr>
    <tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>GO帐号 : </td>
				<td><?
				$form->show('member_login');
				?></td>				

				<td>游戏名称 : <span class="red">*</span></td>
				<td><?
				$form->show('game_name');
				?></td>
			</tr>	
			<tr>
				<td>游戏帐号 : </td>  
				<td><?
				$form->show('game_login');
				?></td>		
				<td>日期 : </td>
				<td><?
				$form->show('game_start_date');
				?></td>		
			</tr>				
			<tr>
				<td>注册IP : </td>
				<td><?
				$form->show('game_regip');
				?></td>			
	
				<td> 至 </td>
				<td><?
				$form->show('game_end_date');
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
</table>
<?
	$form->end();
	
	//如果有合法的提交，则 getAnyPost = true。
	//如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
	$getAnyPost = false;
	if ($form->check()){
		$getAnyPost = true;
	}elseif(!isset($_GET['page'])){
		unset($_SESSION['search_criteria']);
	}
	
	if($myerror->getAny()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	
	if ($getAnyPost || @$_SESSION['search_criteria']['game_name']){
		$rs = new RecordSetControl;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=admin_enq_mem";
		$rs->display_new_button = false;
		$rs->sort_field = "UID";
		$rs->sort_seq = "DESC";
		$rs->col_edit_col = "修改";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['game_name'])){
			$where_sql.= " AND a.GameName = ".$_SESSION['search_criteria']['game_name'];
		}

		if (strlen(@$_SESSION['search_criteria']['member_login'])){
			$where_sql.= " AND m.GOLogin LIKE '%".$_SESSION['search_criteria']['member_login']."%'";
		}
		if (strlen(@$_SESSION['search_criteria']['game_login'])){
			$where_sql.= " AND a.GameAccName LIKE '%".$_SESSION['search_criteria']['game_login']."%'";
		}
		if (strlen(@$_SESSION['search_criteria']['game_regip'])){
			$where_sql.= " AND a.GameAccIP LIKE '%".$_SESSION['search_criteria']['game_regip']."%'";
		}
		
		if (strlen(@$_SESSION['search_criteria']['game_start_date'])){
			if (strlen(@$_SESSION['search_criteria']['game_end_date'])){
				$where_sql.= " AND a.GameAccDate between '".$_SESSION['search_criteria']['game_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['game_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND a.GameAccDate > '".$_SESSION['search_criteria']['game_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['game_end_date'])){
			$where_sql.= " AND a.GameAccDate < '".$_SESSION['search_criteria']['game_end_date']." 23:59:59'";
		}
		
		// if (strlen(@$_SESSION['search_criteria']['member_grp'])){
			// $where_sql.= " AND AdminGrpID = '".$_SESSION['search_criteria']['member_grp']."'";
		// }
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY a.GameAccID DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' tw_gomember_acc a INNER JOIN tw_gomember m ON (a.UID = m.UID )';
		$list_field = ' SQL_CALC_FOUND_ROWS a.GameAccName, a.GameAccDate, a.GameAccIP, m.UID, m.GOLogin ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("ID", "UID");
		$rs->SetRecordCol("GO帐号", "GOLogin");
		$rs->SetRecordCol("游戏帐号", "GameAccName");
		$rs->SetRecordCol("日期", "GameAccDate");
		// $rs->SetRecordCol("身分证", "GameName");
		// $rs->SetRecordCol("电话", "ContactTel");
		$rs->SetRecordCol("注册IP", "GameAccIP");
		// $rs->SetRecordCol("COL 3", "AdminGrpDefaultPermCode");

		// $sort = GENERAL_NO;
		// $edit = GENERAL_YES;
		// $rs->SetRecordCol("", "UID", $sort, $edit,"?act=admin_member_form","id");
		$rs->SetRSSorting('?act=admin_enq_mem');

/*
$cur_page = 0;
if (isset($_POST["page"])){
$cur_page = $_POST["page"] - 1;
}
*/

		$rs->ShowRecordSet($info);
	}

}
?>