<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
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
		'g_id' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['g_id'],
			'minlen' => 1, 
			'maxlen' => 20,
			//'info' => '不填则显示所有货品编号'
			),	
		/*
		'g_type' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['g_type'], 
			),	
		*/	
		'start_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['start_date'], 
			),	
		'end_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['end_date'], 
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
<h1 class="green">查询产品资料<em>在查询中*号为必填项</em></h1>
<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>货品编号 : </td>
				<td><?
				$form->show('g_id');
				?></td>	
            </tr>
            <? /*
				<td>类别 : </td>
				<td><?
				$form->show('g_type');
				?></td>
			</tr>
			*/?>	
			<tr>
				<td>日期 : </td>
				<td><?
				$form->show('start_date');
				?></td>		
                
                <td> 至 </td>
				<td><?
				$form->show('end_date');
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
	
	if ($getAnyPost || isset($_GET['page'])){
		$rs = new RecordSetControl;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=sendform";
		$rs->display_new_button = false;
		$rs->sort_field = "g_id";
		$rs->sort_seq = "DESC";
		$rs->col_edit_col = "详细信息";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['g_id'])){
			$where_sql.= " AND g_id Like '%".$_SESSION['search_criteria']['g_id'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND g_time between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND g_time > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND g_time < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}
				
		// if (strlen(@$_SESSION['search_criteria']['member_grp'])){
			// $where_sql.= " AND AdminGrpID = '".$_SESSION['search_criteria']['member_grp']."'";
		// }
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY id DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' bom';
		$list_field = ' SQL_CALC_FOUND_ROWS id, g_id, g_type, g_time, p_status ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("产品编号", "g_id");
		$rs->SetRecordCol("类别", "g_type");
		$rs->SetRecordCol("日期", "g_time");
		$rs->SetRecordCol("状态", "p_status");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		
		$rs->SetRecordCol("修改", "id", $sort, $edit,"?act=modifyform","modid");
		$rs->SetRecordCol("预览", "id", $sort, $edit,"?act=formdetail","id");
		//$rs->SetRecordCol("PDF", "id", $sort, $edit,"model/pdf.php?pdf=1","id");
		//$rs->SetRecordCol("EXCEL", "id", $sort, $edit,"model/phpexcel.php?excel=1","id");
		$rs->SetRecordCol("批核", "id", $sort, $edit,"?act=modifyform","changeid");
		$rs->SetRecordCol("删除", "id", $sort, $edit,"?act=modifyform","delid");
		$rs->SetRSSorting('?act=searchform');

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
