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
        'pcid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pcid'],
        ),
        'pid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pid'],
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
        'customer' => array(
            'type' => 'select',
            'options' => get_customer_fty(),
            'value' => @$_SESSION['search_criteria']['customer'],
        ),
        'istatus' => array(
            'type' => 'select',
            'options' => get_po_status(),
            'value' => @$_SESSION['search_criteria']['istatus'],
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
<!--h1 class="green">Factory PO<em>* indicates required fields</em></h1-->

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索订单</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>订单编号 : </td>
				<td><? $form->show('pcid'); ?></td>
                <td>产品编号 : </td>
                <td><? $form->show('pid'); ?></td>
			</tr>          	
			<tr>
				<td>起始日期 : </td>
				<td><? $form->show('start_date'); ?></td>
                <td>结束日期 : </td>
				<td><? $form->show('end_date'); ?></td>
			</tr>
            <tr>
                <td>负责人 : </td>
                <td><? $form->show('created_by'); ?></td>
                <td>客户 : </td>
                <td><? $form->show('customer'); ?></td>
            </tr>
            <tr>
                <td>订单状态 : </td>
                <td><? $form->show('istatus'); ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
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
		$rs = new RecordSetControl3;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=searchpurchase";
		$rs->display_new_button = false;
		$rs->sort_field = "pcid";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['pcid'])){
			$where_sql.= " AND p.pcid Like '%".$_SESSION['search_criteria']['pcid'].'%\'';
		}
        if (strlen(@$_SESSION['search_criteria']['pid'])){
            $where_sql.= " AND pitem.pid = '".$_SESSION['search_criteria']['pid']."'";
        }
		if (strlen(@$_SESSION['search_criteria']['attention'])){
			$where_sql.= " AND p.attention Like '%".$_SESSION['search_criteria']['attention'].'%\'';
		}	
		if (strlen(@$_SESSION['search_criteria']['created_by'])){
			$where_sql.= " AND p.created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
		}
        if (strlen(@$_SESSION['search_criteria']['istatus'])){
            $where_sql.= " AND p.istatus Like '%".$_SESSION['search_criteria']['istatus'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['customer'])){
            $where_sql.= " AND p.customer = '".$_SESSION['search_criteria']['customer']."'";
        }
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND p.mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND p.mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND p.mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}
		
		//普通用户只能搜索到自己开的单 与 状态不为D的单
        //20141031 改为用aFtyName来判断了，工厂用户的AdminName字段改为保存用户的名字
		if (!isFtyAdmin()){
			//$where_sql .= " AND p.sid = '".$_SESSION['ftylogininfo']['aName']."'";
			$where_sql .= " AND p.sid = '".$_SESSION['ftylogininfo']['aFtyName']."'";
		}
				
		// echo $where_sql;
		//(D)是未核批， delete是已删除，这里都不显示
		$where_sql.= " AND p.istatus <> 'delete' AND p.istatus <> '(D)' GROUP BY p.pcid ORDER BY p.mark_date DESC ";
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' purchase p left join purchase_item pitem on p.pcid = pitem.pcid';
		$list_field = ' SQL_CALC_FOUND_ROWS p.pcid, p.created_by, p.mark_date, p.istatus, p.send_to, p.customer_po, p.expected_date, p.total, p.customer, p.approved_by ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("订单编号", "pcid");
		$rs->SetRecordCol("客户", "customer");
		$rs->SetRecordCol("客户单号", "customer_po");
		$rs->SetRecordCol("负责人", "created_by");
		$rs->SetRecordCol("日期", "mark_date");
		$rs->SetRecordCol("要求出货日期", "expected_date");
		$rs->SetRecordCol("总额", "total");
		$rs->SetRecordCol("状态", "istatus");
		$rs->SetRecordCol("核准", "approved_by");
		
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("查看/复期", "pcid", $sort, $edit, "?act=viewpurchase","viewid");
		$rs->SetRecordCol("PDF", "pcid", $sort, $edit, "model/viewpurchase_pdf.php?pdf=1","pcid");
		$rs->SetRecordCol("生产单", "pcid", $sort, $edit, "model/viewpurchase_scd_pdf.php?pdf=1", "pcid");
		$rs->SetRSSorting('?act=searchpurchase');

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

