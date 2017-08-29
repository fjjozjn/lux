<?php
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

if( isset($_GET['delid']) && $_GET['delid'] != ''){
	for( $i = 0; $i < count($_SESSION['choose']); $i++){
		if( $_SESSION['choose'][$i] == $_GET['delid']){
			array_splice($_SESSION['choose'], $i, 1);
		}
	}
}

if( isset($_GET['chooseid']) && $_GET['chooseid'] != ''){
	//$rtn = $mysql->qone('select photos from product where pid = ?', $_GET['chooseid']);
	//第一次因为没有session ，所以会报错，这里屏蔽了错误
	if( !@in_array($_GET['chooseid'], $_SESSION['choose'])){	
		$_SESSION['choose'][] = $_GET['chooseid'];
	}
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
<fieldset class="center2col">
<legend class='legend'>Select</legend>
<?
if( isset($_SESSION['choose']) && !empty($_SESSION['choose'])){
	foreach($_SESSION['choose'] as $v){
		if (is_file($pic_path_com.$v) == true) { 
			$arr = getimagesize($pic_path_com.$v);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(150, 100, $pic_width, $pic_height);
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}else{
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}
	}
}else{
	echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
}
?>
</fieldset>
<fieldset class="center2col">
<legend class='legend'>Product</legend>
<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>Product ID : </td>
				<td><?
				$form->show('pid');
				?></td>	
				<td>Description : </td>
				<td><?
				$form->show('description');
				?></td>
			</tr>
            <tr>
				<td>Supplier ID : </td>
				<td><?
				$form->show('sid');
				?></td>	
				<td>Supplier code : </td>
				<td><?
				$form->show('scode');
				?></td>
			</tr>
            <tr>
				<td>Exclusive To : </td>
				<td><?
				$form->show('exclusive_to');
				?></td>	
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>            	
			<tr>
				<td>Start Date : </td>
				<td><?
				$form->show('start_date');
				?></td>		
                
                <td>End Date </td>
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
		$rs->addnew_link = "?act=com-chooseproduct";
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
		$rs->SetRecordCol("Supplier Code", "scode");
		$rs->SetRecordCol("Customer Code", "ccode");
		$rs->SetRecordCol("Cost RMB", "cost_rmb");
		$rs->SetRecordCol("Cost Remark", "cost_remark");
		$rs->SetRecordCol("Exclusive to", "exclusive_to");
		$rs->SetRecordCol("In Date", "in_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("CHOOSE", "photos", $sort, $edit,"?act=com-chooseproduct&page=1","chooseid");
		$rs->SetRSSorting('?act=com-chooseproduct');

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
</fieldset>
