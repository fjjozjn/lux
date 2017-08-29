<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	//引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	
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
		'qid' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['qid'],
			),	
		'send_to' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['send_to'], 
			),	
		'attention' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['attention'], 
			),	
		'created_by' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['created_by'], 
			),			
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
			'value' => 'Search', 
			),	
);
$form->init($formItems);
$form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>
<h1 class="green">QUOTATION<em>* indicates required fields</em></h1>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="right">Quotation NO. : </td>
				<td align="left"><? $form->show('qid'); ?></td>
				<td align="right">To : </td>
				<td align="left"><? $form->show('send_to'); ?></td>
			</tr>
            <tr>
				<td align="right">Attention : </td>
				<td align="left"><? $form->show('attention'); ?></td>
				<td align="right">Created by : </td>
				<td align="left"><? $form->show('created_by'); ?></td>
			</tr>           	
			<tr>
				<td align="right">Start Date : </td>
				<td align="left"><? $form->show('start_date'); ?></td>
                <td align="right">End Date : </td>
				<td align="left"><? $form->show('end_date'); ?></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
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
		$rs = new RecordSetControl2;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=com-searchquotation";
		$rs->display_new_button = false;
		$rs->sort_field = "qid";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['qid'])){
			$where_sql.= " AND qid Like '%".$_SESSION['search_criteria']['qid'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['send_to'])){
			$where_sql.= " AND send_to Like '%".$_SESSION['search_criteria']['send_to'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['attention'])){
			$where_sql.= " AND attention Like '%".$_SESSION['search_criteria']['attention'].'%\'';
		}	
		if (strlen(@$_SESSION['search_criteria']['created_by'])){
			$where_sql.= " AND created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
		}		
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}
		
		//普通用户只能搜索到自己开的单
		if (!isSysAdmin()){
			//$where_sql .= " AND created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\'))';
			$where_sql .= " AND created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE '%".$_SESSION['logininfo']['aName']."%' OR AdminName = '".$_SESSION['logininfo']['aName']."')";
		}		
		// echo $where_sql;
			
		$where_sql.= ' ORDER BY mark_date DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' quotation';
		$list_field = ' SQL_CALC_FOUND_ROWS qid, send_to, mark_date, created_by, total ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("Quotation ID", "qid");
		$rs->SetRecordCol("To", "send_to");
		$rs->SetRecordCol("Total", "total");
		$rs->SetRecordCol("Created by", "created_by");
		$rs->SetRecordCol("Date", "mark_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("PDF", "qid", $sort, $edit,"model/com/quotation_pdf.php?pdf=1","qid");
		//$rs->SetRecordCol("ADD TO PROFORMA", "qid", $sort, $edit,"?act=com-modifyproforma","qid");
		$rs->SetRecordCol("MODIFY", "qid", $sort, $edit,"?act=com-modifyquotation","modid");
		$rs->SetRecordCol("DEL", "qid", $sort, $edit,"?act=com-modifyquotation","delid");
		$rs->SetRSSorting('?act=com-searchquotation');

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

