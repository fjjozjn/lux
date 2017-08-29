<?php
// print_r_pre($_SESSION);

//check permission 
//checkAdminPermission(PERM_VIEW_ADMIN);
//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}

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
	$mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
	$temp_grp = $mysql->fetch(0,1);	
	for($i = 0 ; $i < count($temp_grp); $i++){
		$temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
		$row_grp[] = $temp;
	}
	// print_r_pre($temp_grp);
	$form = new My_Forms();
	$formItems = array(
			'admin_name' => array(
				'type' => 'select',
                'options' => $user,
				'value' => @$_SESSION['search_criteria']['admin_name'], 
				),
            'action' => array(
                'type' => 'text',
                'value' => @$_SESSION['search_criteria']['action'],
            ),
/*			'admin_login' => array(
				'type' => 'text', 
				'value' => @$_SESSION['search_criteria']['admin_login'], 
				),	*/
			'admin_log_start_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['admin_log_start_date'], 
				),			
			'admin_log_end_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['admin_log_end_date'], 
				),
/*			'admin_grp' => array(
				'type' => 'select', 			
				'value' => @$_SESSION['search_criteria']['admin_grp'],			
				'options' => $row_grp,			
				),	*/
			'submitbutton' => array(
				'type' => 'submit', 
				'value' => ' Search ', 
				'title' => ''),
			);
	$form->init($formItems);
	$form->begin();
	
	
	// resetJSForm('text', 'admin_name');
	// print_r_pre($row_grp);
	// print_r_pre($_GET);
	// print_r_pre($GLOBALS);
	?>
	
	<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">User log</td>
		</tr>
		<tr>
		<td align="center">	
		<fieldset>
		<legend class='legend'>search</legend>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<tr>
<!--					<td height="35">Account : </td>
				    <td><?/* $form->show('admin_login');*/?></td>-->
					<td>User Name : </td>
					<td><? $form->show('admin_name');?></td>
                    <td>Log : </td>
                    <td><? $form->show('action');?></td>
				</tr>	
				<tr>
					<td height="35">Start Time : </td>  
				    <td><? $form->show('admin_log_start_date');?></td>
					<td>End Time : </td>
					<td><? $form->show('admin_log_end_date');?></td>
				</tr>
				<!--tr>
					<td height="35">管理员群组 : </td>
				    <td><? //$form->show('admin_grp');?></td>
					<td>&nbsp;</td>  
					<td>&nbsp;</td>
				</tr-->				
				<tr>
					<td width="100%" colspan='4'><? $form->show('submitbutton');?></td>
				</tr>				
			</table>
		</fieldset>	
		</td>	
		</tr>
	</table><br />
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
	
	$rs = new RecordSetControl2;
	$rs->record_per_page = ADMIN_ROW_PER_PAGE;
	$rs->addnew_link = "?act=main";
	$rs->display_new_button = false;
	$rs->sort_field = "AdminHistDate";
	$rs->sort_seq = "DESC";
	$rs->col_edit_col = "修改";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}
	
	//20121025 暂时只显示 login 和 logout 的日志
    //20140102 加显示所有action_log_日志
	$where_sql = " AND (h.AdminHistCatg in ('login', 'logout') OR h.AdminHistCatg LIKE 'action_log_%')";
	
	if (strlen(@$_SESSION['search_criteria']['admin_log_start_date'])){
		if (strlen(@$_SESSION['search_criteria']['admin_log_end_date'])){
			$where_sql.= " AND h.AdminHistDate between '".$_SESSION['search_criteria']['admin_log_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['admin_log_end_date']." 23:59:59'";
		}else{
			$where_sql.= " AND h.AdminHistDate > '".$_SESSION['search_criteria']['admin_log_start_date']." 00:00:00'";
		}
	}elseif (strlen(@$_SESSION['search_criteria']['admin_log_end_date'])){
		$where_sql.= " AND h.AdminHistDate < '".$_SESSION['search_criteria']['admin_log_end_date']." 23:59:59'";
	}
	if (strlen(@$_SESSION['search_criteria']['admin_name'])){
		$where_sql.= " AND a.AdminName LIKE '%".$_SESSION['search_criteria']['admin_name']."%'";
	}
    if (strlen(@$_SESSION['search_criteria']['action'])){
        $where_sql.= " AND h.AdminHistRemark LIKE '%".$_SESSION['search_criteria']['action']."%'";
    }
/*	if (strlen(@$_SESSION['search_criteria']['admin_login'])){
		$where_sql.= " AND a.AdminLogin LIKE '%".$_SESSION['search_criteria']['admin_login']."%'";
	}*/
	/*
	if (strlen(@$_SESSION['search_criteria']['admin_grp'])){
		$where_sql.= " AND a.AdminGrpID = '".$_SESSION['search_criteria']['admin_grp']."'";
	}
	*/
	// echo $where_sql;
	
	//if($where_sql){

        //我自己的帐号zjn的日志不显示
		$where_sql.= ' AND h.AdminID <> 4 ORDER BY h.AdminHistID DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
		
		// $temp_table = '  ';
		$temp_table = ' tw_admin_hist h JOIN tw_admin a ON (h.AdminID = a.AdminID) ';
		$temp_table.= ' JOIN tw_admingrp g ON (a.AdminGrpID = g.AdminGrpID)';
		
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		
		$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'");<BR>';
		
		//$rs->col_width = "5%";
		//$rs->SetRecordCol("Log ID", "AdminHistID");
		$rs->col_width = "15%";
		$rs->SetRecordCol("Login Date", "AdminHistDate");
		//$rs->col_width = "10%";
		//$rs->SetRecordCol("Account", "AdminLogin");
		$rs->col_width = "10%";
		$rs->SetRecordCol("User Name", "AdminName");
		//$rs->col_width = "10%";
		//$rs->SetRecordCol("管理群组", "AdminGrpName");
		// $rs->col_width = "10%";
		// $rs->SetRecordCol("管理群组", "AdminHistAction");
		$rs->col_width = "30%";
		$rs->SetRecordCol("Log", "AdminHistRemark");
		$rs->col_width = "10%";
		$rs->SetRecordCol("IP", "AdminHistIP");
		$rs->SetRSSorting('?act=admin_log');
		
		/*
		$cur_page = 0;
		if (isset($_POST["page"])){
		$cur_page = $_POST["page"] - 1;
		}
		*/
		
		$rs->ShowRecordSet($info);
	//}
}
?>