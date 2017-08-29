<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/

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
        'pcid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pcid'],
        ),
        'd_id' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['d_id'],
            'info' => '由于一个出货单中可能有多个PO<br />所以搜索结果中可能会出现多条相同出货单号的记录'
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
            'value' => '确定',
        ),
    );
    $form->init($formItems);
$form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
?>
<!--h1 class="green"><em>* indicates required fields</em></h1-->

<table width="70%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索出货单</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">        	
            <tr valign="top">
				<td>订单号 : </td>
				<td><? $form->show('pcid'); ?></td>
				<td>出货单号 : </td>
				<td><? $form->show('d_id'); ?></td>
			</tr>
            <tr>
                <td>起始日期 : </td>
                <td><? $form->show('start_date'); ?></td>
                <td>结束日期 : </td>
                <td><? $form->show('end_date'); ?></td>
            </tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'><? $form->show('submitbutton'); ?></td>
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
		$rs = new RecordSetControl3;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=searchdelivery";
		$rs->display_new_button = false;
		$rs->sort_field = "d_id";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";
        //默认情况下用d_id分组，当搜索d_id的时候，因为有存在多个对应的po_id，所以以po_id分组
        $group = "group by d.d_id";

        if (strlen(@$_SESSION['search_criteria']['pcid'])){
            $where_sql.= " AND di.po_id Like '%".$_SESSION['search_criteria']['pcid'].'%\'';
        }
		if (strlen(@$_SESSION['search_criteria']['d_id'])){
			$where_sql.= " AND d.d_id Like '%".$_SESSION['search_criteria']['d_id'].'%\'';
            //以item表的po_id分组，这样搜索结果中就有可能会出现相同d_id不同po_id的几条记录
            $group = "group by di.po_id";
		}
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND d.d_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND d.d_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND d.d_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}
		
		//普通用户只能搜索到自己开的单
		if (!isFtyAdmin()){
			$where_sql .= " AND d.sid = '".$_SESSION['ftylogininfo']['aFtyName'].'\'';
		}
				
		// echo $where_sql;
		
		$where_sql.= ' AND d.d_id = di.d_id AND d.sid = s.sid '.$group.' ORDER BY d.in_date DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;

		//$temp_table = ' delivery d, purchase p';
		//$list_field = ' SQL_CALC_FOUND_ROWS d.d_id, d.d_date, d.sid, p.expected_date ';
		$temp_table = ' delivery d, supplier s, delivery_item di';
		$list_field = ' SQL_CALC_FOUND_ROWS d.d_id, d.d_date, d.in_date, d.total_all, s.name, di.po_id ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("工厂名", "name");
		$rs->SetRecordCol("工厂出货单号", "d_id");
		$rs->SetRecordCol("订单号", "po_id");
		$rs->SetRecordCol("出货日期", "d_date");
		$rs->SetRecordCol("开单日期", "in_date");
		//$rs->SetRecordCol("出货期限", "expected_date");
		//$rs->SetRecordCol("工厂编号", "sid");
		$rs->SetRecordCol("总金额", "total_all");
		
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		//有時太方便反而令人手容易錯
		//$rs->SetRecordCol("ADD TO PURCHASE", "po_id", $sort, $edit,"?act=com-modifypurchase","po_id");
		//$rs->SetRecordCol("ADD TO INVOICE", "po_id", $sort, $edit,"?act=com-modifyinvoice","po_id");
		$rs->SetRecordCol("出货清单", "d_id", $sort, $edit,"model/delivery_pdf.php?pdf=1","d_id");
		$rs->SetRecordCol("出货发票", "d_id", $sort, $edit,"model/delivery_split_pdf.php?pdf=1","d_id");
		$rs->SetRecordCol("产品送检单", "d_id", $sort, $edit,"model/delivery_check_pdf.php?pdf=1","d_id");
		$rs->SetRecordCol("修改", "d_id", $sort, $edit,"?act=modifydelivery","modid");
		$rs->SetRecordCol("删除", "d_id", $sort, $edit,"?act=modifydelivery","delid");
		$rs->SetRSSorting('?act=searchdelivery');

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

