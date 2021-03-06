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
        'pl_id' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pl_id'],
        ),
        'vid' => array(
            'type' => 'text',
            //'type' => 'select',
            //'options' => get_invoice_no(),
            'value' => @$_SESSION['search_criteria']['vid'],
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
<h1 class="green">Packing List<em>* indicates required fields</em></h1>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="right">Packing List No. : </td>
				<td align="left"><? $form->show('pl_id'); ?></td>
				<td align="right">Invoice No. : </td>
				<td align="left"><? $form->show('vid'); ?></td>
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
		$rs->addnew_link = "?act=com-searchpackinglist";
		$rs->display_new_button = false;
		//$rs->sort_field = "in_date";
		//$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = '';
		if (strlen(@$_SESSION['search_criteria']['pl_id'])){
			$where_sql.= " AND pl.pl_id Like '%".$_SESSION['search_criteria']['pl_id'].'%\'';
		}
        if (strlen(@$_SESSION['search_criteria']['vid'])){
            $where_sql.= " AND pli.ref like '%".$_SESSION['search_criteria']['vid']."%'";
        }
		$where_sql .= " group by pl.pl_id order by pl.in_date desc";
		// echo $where_sql;

		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' packing_list pl left join packing_list_item pli on pl.pl_id = pli.pl_id ';
		$list_field = ' SQL_CALC_FOUND_ROWS pl.pl_id, pl.ship_to, pl.total_cart, pl.total_qty, pl.total_weight,
		pl.total_cbm, pl.in_date ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("Packing List No.", "pl_id");
		$rs->SetRecordCol("Ship To", "ship_to");	
		$rs->SetRecordCol("Total Carton", "total_cart");
		$rs->SetRecordCol("Total Quantity", "total_qty");
		$rs->SetRecordCol("Total Weight", "total_weight");
		$rs->SetRecordCol("Total CBM(m³)", "total_cbm");
		$rs->SetRecordCol("Created Date", "in_date");
		
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		
		$rs->SetRecordCol("PDF", "pl_id", $sort, $edit,"model/com/packing_list_pdf.php?pdf=1", "pl_id");
		$rs->SetRecordCol("EXCEL", "pl_id", $sort, $edit,"model/com/packing_list_excel.php?excel=1", "pl_id");
		$rs->SetRecordCol("MODIFY", "pl_id", $sort, $edit,"?act=com-modifypackinglist", "modid");
		$rs->SetRecordCol("DEL", "pl_id", $sort, $edit,"?act=com-modifypackinglist", "delid");
		$rs->SetRSSorting('?act=com-searchpackinglist');

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

