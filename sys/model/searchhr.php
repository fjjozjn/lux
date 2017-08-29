<?php
// print_r_pre($_SESSION);

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//die();
//checkAdminPermission(PERM_VIEW_ADMIN);
 
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
    $hr_setting = mysql_qone('select al_start_days, al_end_days, al_increase_days from setting');

    //引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
		
	// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
		$_GET['page'] = 1;
	}
	
	$form = new My_Forms();
    $formItems = array(
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
        'created_by' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['created_by'],
        ),
        'type' => array(
            'type' => 'select',
            'options' => get_hr_type(2),
            'value' => @$_SESSION['search_criteria']['type'],
        ),
        'is_approve' => array(
            'type' => 'select',
            'options' => array(array('Approve', '1'), array('Not Approve', '0')),
            'value' => @$_SESSION['search_criteria']['is_approve'],
        ),

        'submitbutton' => array(
            'type' => 'submit',
            'value' => 'Search'
        ),
    );
	$form->init($formItems);
	$form->begin();
	
	?>
	
<table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">OT/Annual Leave</td>
		</tr>
		<tr>
		<td align="center">	
		<fieldset>
		<legend class='legend'>Search</legend>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="right">Start Created Date : </td>
					<td align="left"><? $form->show('start_date'); ?></td>
					<td align="right">End Created Date : </td>
					<td align="left"><? $form->show('end_date'); ?></td>
				</tr>
                <tr>
                    <td align="right">Created by : </td>
                    <td align="left"><? $form->show('created_by'); ?></td>
                    <td align="right">Type : </td>
                    <td align="left"><? $form->show('type'); ?></td>
                </tr>
                <tr>
                    <td align="right">Approve : </td>
                    <td align="left"><? $form->show('is_approve'); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
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
<br />
<br />

<div align="left" style="color: #FF0000; width: 60%; padding-left: 315px;">
    <b>注：</b>
    <br />
    (1)管理员审批了请假或加班申请后，请假或加班的小时数才会更新在总小时数里。
    <br />
    (2)从2013.10.1日起年假制度更新：员工入职后，年假小时数为0，每个月按入职年限加上小时数。计算方法是：入职第一年每月加年假小时数为 <?=$hr_setting['al_start_days']*8?>/12 小时，入职第二年是 <?=($hr_setting['al_start_days']+$hr_setting['al_increase_days'])*8?>/12 小时，以此类推，最多<?=$hr_setting['al_end_days']*8?>/12 小时。请按前面的规则核对自己的总小时数是否正常，如果不是请联系管理员修改。年假小时数将在每月你入职那天加上，但如果本月只有30天，而你是某月31号入职，年假的小时数将在本月最后一天加上。总小时数允许透支，即可以是负数。
    <br />
    (3)以后加班或请假或每个月的加年假，也都请大家核对总小时数，以及扣或加小时数的情况，如果有问题，请及时向管理员反映。
    <br />
    (4)申請加班只能在正常工作日9:00到18:00以外時間, 每天最多算8小时。
    <br />
    <br />
</div>

<table width="600" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
	<tr height="30" bgcolor='#EEEEEE'>
    	<td>User Name</td>
        <td>Job Commencement Date</td>
    	<td>Worked year(s)</td>
    	<td>Monthly Increase Hours</td>
       	<td>Total Hours</td>
       	<td>Total Leave hours</td>
       	<td>Total OT hours</td>
	</tr>
<?
    //20130726 只显示 AdminEnabled = 1 的
    //20140503 不显示只有fty的
    //20141105 也不显示只有luxcraft的
	if(isSysAdmin()){
		$rs = $mysql->q('select AdminName, AdminTotalHours, AdminJoinDate from tw_admin where AdminName <> ? and AdminEnabled = 1 and AdminPlatform <> ? and AdminPlatform <> ?', 'ZJN', 'fty', 'luxcraft');
	}else{
		$rs = $mysql->q('select AdminName, AdminTotalHours, AdminJoinDate from tw_admin where AdminID = ? and AdminEnabled = 1 and AdminPlatform <> ? and AdminPlatform <> ?', $_SESSION['logininfo']['aID'], 'fty', 'luxcraft');
	}
	
	if($rs){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){

            //worked years : 入职时间（以后刚入职年假为0，按月加年假的小时数）
            $worked_years = round(((time() - strtotime($v['AdminJoinDate'])) / 31536000), 1);
            //Total Leave hours
            $total_leave_hours = 0;
            //Total OT hours
            $total_ot_hours = 0;

            $rs = $mysql->q('select type, total_hours from hr_log where created_by = ? and is_approve = 1', $v['AdminName']);
            if($rs){
                $rtn_hr = $mysql->fetch();
                foreach($rtn_hr as $x){
                    if($x['type'] == 'LEAVE'){
                        $total_leave_hours += $x['total_hours'];
                    }elseif($x['type'] == 'OT'){
                        $total_ot_hours += $x['total_hours'];
                    }
                }
            }

            //Monthly Increase Hours
            //$worked_month = intval((time() - strtotime($v['AdminJoinDate'])) / 2592000);
            $hr_setting = $mysql->qone('select al_start_days, al_end_days, al_increase_days from setting');
            if(isset($hr_setting['al_start_days']) && $hr_setting['al_start_days'] != '' && isset($hr_setting['al_end_days']) && $hr_setting['al_end_days'] != '' && isset($hr_setting['al_increase_days']) && $hr_setting['al_increase_days'] != ''){
                $base_hours = 0;
                if($hr_setting['al_start_days'] + intval($worked_years) > $hr_setting['al_end_days']){
                    $base_hours = $hr_setting['al_end_days'] * 8;
                }else{
                    $base_hours = ($hr_setting['al_start_days'] + intval($worked_years)) * 8;
                }
                $monthly_increase_hours = round(($base_hours / 12), 2);
            }

			echo '<tr class="td_" align="center" onMouseOver="this.className=\'td_highlight\';" onMouseOut="this.className=\'td_\';" valign="top">'.
                '<td>'.$v['AdminName'].'</td>'.
                '<td>'.$v['AdminJoinDate'].'</td>'.
                '<td>'.$worked_years.'</td>'.
                '<td>'.$monthly_increase_hours.'</td>'.
                '<td>'.redFont($v['AdminTotalHours']).'</td>'.
                '<td>'.$total_leave_hours.'</td>'.
                '<td>'.$total_ot_hours.'</td>'.
                '</tr>';
		}
	}else{
		echo '<tr><td colspan="2">No Record!</td></tr>';
	}
?>
</table>
<br />
	<?
	$form->end();
	
	//如果有合法的提交，則 getAnyPost = true。
	//如果不是翻頁而是普通的GET，則清除之前的Session，以顯示一個空白的表單
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
	$rs->display_new_button = false;
	$rs->sort_field = "id";
	$rs->sort_seq = "DESC";
	
	$current_page = 1;
	$start_row = 0;
	$end_row = $rs->record_per_page;
	if (set($_GET['page'])){
		$current_page = intval($_GET['page']);
		$start_row = (($current_page-1) * $rs->record_per_page);
	}
	
	$where_sql = "";
	if (strlen(@$_SESSION['search_criteria']['start_date'])){
		if (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND h.in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}else{
			$where_sql.= " AND h.in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
		}
	}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
		$where_sql.= " AND h.in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
	}
    if (strlen(@$_SESSION['search_criteria']['created_by'])){
        $where_sql.= " AND h.created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
    }
    if (strlen(@$_SESSION['search_criteria']['type'])){
        $where_sql.= " AND h.type Like '%".$_SESSION['search_criteria']['type'].'%\'';
    }
    if (strlen(@$_SESSION['search_criteria']['is_approve'])){
        $where_sql.= " AND h.is_approve Like '%".$_SESSION['search_criteria']['is_approve'].'%\'';
    }

	if(!isSysAdmin()){
		$where_sql .= " and h.created_by = '".$_SESSION['logininfo']['aName']."'";
	}else{
        $where_sql .= " and h.created_by <> 'ZJN'";
    }

    //20130726 只显示 AdminEnabled = 1 的
    //20140503 不显示只有fty的
    //20141105 也不显示只有luxcraft的
	$where_sql .= " and t.AdminEnabled = 1 and t.AdminPlatform <> 'fty' and t.AdminPlatform <> 'luxcraft' order by h.in_date desc";
	
	$_SESSION['search_criteria']['page'] = $current_page;
	$temp_table = ' hr_log h left join tw_admin t on h.created_by = t.AdminName ';
	
	//get the row count for this seaching criteria
	//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
			
	$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
	// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
	//$rs->col_width = "100";
	$rs->SetRecordCol("User Name", "created_by");
	$rs->SetRecordCol("Created Date", "in_date");
	$rs->SetRecordCol("Type", "type");
	$rs->SetRecordCol("Start Date", "start_date");
	$rs->SetRecordCol("End Date", "end_date");
	$rs->SetRecordCol("Hours", "total_hours");
	$rs->SetRecordCol("Reason", "remark");
	$rs->SetRecordCol("Approve", "is_approve");

	$sort = GENERAL_NO;
	$edit = GENERAL_YES;
	
	//20130217 不能在这里用 isSysAdmin 因为里面有select语句，会替代了上面的 backend_list_withfield 找出的数据，导致数据都不见了
	if ($_SESSION['logininfo']['aName'] == 'ZJN' || $_SESSION['logininfo']['aName'] == 'KEVIN'){
		$rs->SetRecordCol("HR_APPROVE", "id", $sort, $edit, "?act=managehr", "approveid");
	}
	$rs->SetRecordCol("DEL", "id", $sort, $edit, "?act=managehr", "delid");
	$rs->SetRSSorting('?act=searchhr');
		
	$rs->ShowRecordSet($info);
}
?>