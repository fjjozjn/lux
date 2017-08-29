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
		'fty_id' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['fty_id'], 
			),				
		'fty_desc' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['description'], 
			),	
		'fty_customer_code' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['fty_customer_code'], 
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
			'value' => ' 搜索 ', 
			),	
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
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>产品编号 : </td>
				<td><?
				$form->show('fty_id');
				?></td>	
				<td>产品描述 : </td>
				<td><?
				$form->show('fty_desc');
				?></td>
			</tr>  
			<tr>
				<td>客户编号 : </td>
				<td><?
				$form->show('fty_customer_code');
				?></td>		
                <td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>			        	
			<tr>
				<td>日期 : </td>
				<td><?
				$form->show('start_date');
				?></td>		
                <td>到 : </td>
				<td><?
				$form->show('end_date');
				?></td>
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
		//$rs->addnew_link = "?act=addproduct";
		$rs->display_new_button = false;
		//$rs->sort_field = "pid";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['fty_id'])){
			$where_sql.= " AND fty_id Like '%".$_SESSION['search_criteria']['fty_id'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['fty_desc'])){
			$where_sql.= " AND fty_desc Like '%".$_SESSION['search_criteria']['fty_desc'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['fty_customer_code'])){
			$where_sql.= " AND fty_customer_code Like '%".$_SESSION['search_criteria']['fty_customer_code'].'%\'';
		}				
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND fty_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND fty_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND fty_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}		
		// echo $where_sql;
		
		//只显示fty_isin为0的，即是还未插入sys product表的数据记录
		$where_sql.= " AND fty_isin = 0 ORDER BY fty_date DESC ";
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' fty_product';
		$list_field = ' SQL_CALC_FOUND_ROWS * ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("产品图片", "fty_photo");
		$rs->SetRecordCol("供应商", "fty_sid");
		$rs->SetRecordCol("产品编号", "fty_id");		
		$rs->SetRecordCol("产品描述", "fty_desc");
		$rs->SetRecordCol("价格", "fty_cost");
		$rs->SetRecordCol("客户编号", "fty_customer_code");
		$rs->SetRecordCol("时间", "fty_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("INSERT", array("fty_id","fty_sid"), $sort, $edit,"?act=com-insertproduct&page="
            .$_GET['page'],
            array("inid",
            "insid"));
		//$rs->SetRecordCol("修改", "fty_id", $sort, $edit,"?act=modifyproduct","modid");
		//$rs->SetRecordCol("删除", "fty_id", $sort, $edit,"?act=modifyproduct","delid");
		$rs->SetRSSorting('?act=com-fty_searchproduct');

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
<? //下面這BR是為了方便顯示表格下部分的預覽圖，以後要能自動判斷瀏覽器邊界來顯示預覽圖 ?>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

