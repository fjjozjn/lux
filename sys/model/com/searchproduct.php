<?php

die('Please use the new page to modify product !');

// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	
	//已用新DB，引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	//$luxmysql = new My_Mysql($luxDbInfo);

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
		'pid' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['pid'],
			),	
		'description' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['description'], 
			),	
		'sid' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['sid'], 
			),	
		'scode' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['scode'], 
			),	
		'ccode' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['ccode'], 
			),				
		'exclusive_to' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['exclusive_to'], 
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
		'show_in_catalog' => array(
			'type' => 'select',
			'options' => array(array('Yes', 1), array('No', 0)),
			'value' => @$_SESSION['search_criteria']['show_in_catalog']
			),	
		'theme' => array(
			'type' => 'select',
			'options' => $theme,
			'value' => @$_SESSION['search_criteria']['theme']
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
<h1 class="green">PRODUCT<em>* indicates required fields</em></h1>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="right">Product ID : </td>
				<td align="left"><? $form->show('pid'); ?></td>
				<td align="right">Description : </td>
				<td align="left"><? $form->show('description'); ?></td>
			</tr>
            <tr>
				<td align="right">Supplier ID : </td>
				<td align="left"><? $form->show('sid'); ?></td>
				<td align="right">Exclusive To : </td>
				<td align="left"><? $form->show('exclusive_to'); ?></td>
			</tr>
            <tr>
				<td align="right">Supplier code : </td>
				<td align="left"><? $form->show('scode'); ?></td>
				<td align="right">Customer code : </td>
				<td align="left"><? $form->show('ccode'); ?></td>
			</tr>            	
			<tr>
				<td align="right">Start Date : </td>
				<td align="left"><? $form->show('start_date'); ?></td>
                <td align="right">End Date : </td>
				<td align="left"><? $form->show('end_date'); ?></td>
			</tr>
			<tr>
				<td align="right">Show in catalog : </td>
				<td align="left"><? $form->show('show_in_catalog'); ?></td>
                <td align="right">Theme : </td>
				<td align="left"><? $form->show('theme'); ?></td>
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
		$rs->addnew_link = "?act=com-searchproduct";
		$rs->display_new_button = false;
		$rs->sort_field = "pid";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['pid'])){
			$where_sql.= " AND pid Like '%".$_SESSION['search_criteria']['pid'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['description'])){
			$where_sql.= " AND description Like '%".$_SESSION['search_criteria']['description'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['sid'])){
			$where_sql.= " AND sid Like '%".$_SESSION['search_criteria']['sid'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['scode'])){
			$where_sql.= " AND scode Like '%".$_SESSION['search_criteria']['scode'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['ccode'])){
			$where_sql.= " AND ccode Like '%".$_SESSION['search_criteria']['ccode'].'%\'';
		}		
		if (strlen(@$_SESSION['search_criteria']['exclusive_to'])){
			$where_sql.= " AND exclusive_to Like '%".$_SESSION['search_criteria']['exclusive_to'].'%\'';
		}		
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}
		if (strlen(@$_SESSION['search_criteria']['show_in_catalog'])){
			$where_sql .= " AND show_in_catalog = ".$_SESSION['search_criteria']['show_in_catalog'];
		}
		if (strlen(@$_SESSION['search_criteria']['theme'])){
			$where_sql .= " AND theme = ".$_SESSION['search_criteria']['theme'];
		}		
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY in_date DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' product';
		$list_field = ' SQL_CALC_FOUND_ROWS * ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("Photos", "photos");
		$rs->SetRecordCol("Product ID", "pid");
		$rs->SetRecordCol("Description", "description");
		$rs->SetRecordCol("Desc(Chi)", "description_chi");
		$rs->SetRecordCol("Supplier ID", "sid");
		$rs->SetRecordCol("Supplier Product Code", "scode");
		$rs->SetRecordCol("Customer Code", "ccode");
		$rs->SetRecordCol("Cost RMB", "cost_rmb");
		$rs->SetRecordCol("Cost Remark", "cost_remark");
		$rs->SetRecordCol("Exclusive to", "exclusive_to");
		$rs->SetRecordCol("Show In Catalog", "show_in_catalog");
		$rs->SetRecordCol("Last Modify Date", "in_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("HISTORY", "pid", $sort, $edit,"?a=1","pid");
		$rs->SetRecordCol("MODIFY", "pid", $sort, $edit,"?act=com-modifyproduct","modid");
		$rs->SetRecordCol("DEL", "pid", $sort, $edit,"?act=com-modifyproduct","delid");
		$rs->SetRSSorting('?act=com-searchproduct');

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
<!--br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /-->

