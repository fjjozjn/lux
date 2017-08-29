<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/innamee_warn.php');	
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
		'cid' => array(
			'type' => 'text',
			'value' => @$_SESSION['search_criteria']['cid'],
			),
		'name' => array(
			'type' => 'text',
			'value' => @$_SESSION['search_criteria']['name'],
			),
		'website' => array(
			'type' => 'text',
			'value' => @$_SESSION['search_criteria']['website'],
			),
        'country' => array(
            'type' => 'select',
            'options' => get_all_country(),
            'value' => @$_SESSION['search_criteria']['country'],
        ),
        'existing_customer' => array(
            'type' => 'select',
            'options' => array(array('Yes', 'Yes'), array('No', 'No')),
            'value' => @$_SESSION['search_criteria']['existing_customer'],
        ),
		'created_by' => array(
			'type' => 'text',
			'value' => @$_SESSION['search_criteria']['created_by'],
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
<h1 class="green">CUSTOMER<em>* indicates required fields</em></h1>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="right">Customer ID : </td>
				<td align="left"><? $form->show('cid'); ?></td>
				<td align="right">Name : </td>
				<td align="left"><? $form->show('name'); ?></td>
			</tr>
            <tr>
				<td align="right">Website : </td>
				<td align="left"><? $form->show('website'); ?></td>
				<td align="right">Country : </td>
				<td align="left"><? $form->show('country'); ?></td>
			</tr>
            <tr>
                <td align="right">Existing Customer : </td>
                <td align="left"><? $form->show('existing_customer'); ?></td>
                <td align="right">Created by : </td>
                <td align="left"><? $form->show('created_by'); ?></td>
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
		$rs->addnew_link = "?act=com-searchcustomer";
		$rs->display_new_button = false;
		$rs->sort_field = "cid";
		$rs->sort_seq = "DESC";

		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}

		$where_sql = "";

		if (strlen(@$_SESSION['search_criteria']['cid'])){
			$where_sql.= " AND c.cid Like '%".$_SESSION['search_criteria']['cid'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['name'])){
			$where_sql.= " AND c.name Like '%".$_SESSION['search_criteria']['name'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['website'])){
			$where_sql.= " AND c.website Like '%".$_SESSION['search_criteria']['website'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['created_by'])){
			$where_sql.= " AND c.created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
		}
        if (strlen(@$_SESSION['search_criteria']['country'])){
            $where_sql.= " AND c.country Like '%".$_SESSION['search_criteria']['country'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['existing_customer']) && $_SESSION['search_criteria']['existing_customer'] == 'Yes'){
            $where_sql.= " AND pi.pvid is not null";
        }else if(strlen(@$_SESSION['search_criteria']['existing_customer']) && $_SESSION['search_criteria']['existing_customer'] == 'No'){
            $where_sql.= " AND pi.pvid is null";
        }
		//普通用户只能搜索到自己添加的customer
		if (!isSysAdmin()){
            //20150601 修改为普通用户只能查看自己group的用户的信息，也就是主可以查附属的，附属的不能查主的
			//$where_sql .= " AND created_by in (SELECT AdminName FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."' OR AdminLuxGroup = '".$_SESSION['logininfo']['aName']."')";

            //20150626 把条件in里的子查询拿出来在外面拼接好，之前可以查出数据，但是担心以后某个时间会出问题，所以也改了
            $mysql->q('SELECT AdminName FROM tw_admin WHERE AdminName = ? OR AdminLuxGroup like ?', $_SESSION['logininfo']['aName'], '%'.$_SESSION['logininfo']['aName'].'%');
            $rtn = $mysql->fetch();
            $temp_where = '';
            foreach($rtn as $v){
                $temp_where .= "'".$v['AdminName']."',";
            }
            $temp_where = trim($temp_where, ',');
            $where_sql .= " AND c.created_by in (".$temp_where.")";
		}		
		
		$where_sql.= ' group by c.cid ORDER BY c.cid ';
		$_SESSION['search_criteria']['page'] = $current_page;

		$temp_table = ' customer c left join proforma pi on c.cid = pi.cid';
		$list_field = ' SQL_CALC_FOUND_ROWS c.* ';

		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

		//$rs->col_width = "100";
		$rs->SetRecordCol("Customer ID", "cid");
		$rs->SetRecordCol("Name", "name");
        $rs->SetRecordCol("Country", "country");
		$rs->SetRecordCol("Website", "website");
		$rs->SetRecordCol("Remark", "remark");
		$rs->SetRecordCol("Markup Ratio", "markup_ratio");
		//$rs->SetRecordCol("Terms", "terms");
		$rs->SetRecordCol("Deposit ( % )", "deposit");
		$rs->SetRecordCol("Balance ( day )", "balance");
		$rs->SetRecordCol("Created by", "created_by");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("MODIFY", "cid", $sort, $edit,"?act=com-modifycustomer","modid");
		//$rs->SetRecordCol("ADD", "cid", $sort, $edit,"?act=com-c_addcontact","cid");
		//20150622
        //$rs->SetRecordCol("VIEW CONTACT", "cid", $sort, $edit,"?act=com-c_all_contact","cid");
        //20151213
        $rs->SetRecordCol("VIEW CONTACT", "cid", $sort, $edit,"?act=com-c_searchcontact&page=1","cid");
		$rs->SetRecordCol("DEL", "cid", $sort, $edit,"?act=com-modifycustomer","delid");
		$rs->SetRSSorting('?act=com-searchcustomer');

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

