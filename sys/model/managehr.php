<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//规范化周日的表达
$wkday_ar=array(
    array('日','休息日'),
    array('一','工作日'),
    array('二','工作日'),
    array('三','工作日'),
    array('四','工作日'),
    array('五','工作日'),
    array('六','休息日')
);
$hr_log_history = '';	
$sign = false;

if (isset($_GET['delid']) && isId($_GET['delid'])){
	if(isSysAdmin()){
		$rtn = $mysql->qone('select type, total_hours, created_by from hr_log where id = ? and is_approve = 1', $_GET['delid']);
		$rs_del = $mysql->q('delete from hr_log where id = ?', $_GET['delid']);
		if($rs_del){
			$sign = true;
			$hr_log_history = 'Delete hr log success! (201301221049)';	
			$myerror->ok($hr_log_history, 'searchhr&page=1');
			//未审批的直接删，已审批的要更新总小时数
			if($rtn){
				if($rtn['type'] == 'OT'){
					$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours - ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);	
					if($rs){
						$hr_log_history .= ' and update total hours success(ot)! (201301221725)';
					}else{
						$hr_log_history .= ' and update total hours failure(ot)! (201301221726)';
					}
				}elseif($rtn['type'] == 'LEAVE'){
					$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours + ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);	
					if($rs){
						$hr_log_history .= ' and update total hours success(leave)! (201301221727)';
					}else{
						$hr_log_history .= ' and update total hours failure(leave)! (201301221728)';
					}
				}else{
					$hr_log_history .= 'Delete hr log failure! (201301221721)';	
					$myerror->error($hr_log_history, 'searchhr&page=1');						
				}
			}
		}else{
			$hr_log_history = 'Delete hr log failure! (201301221048)';
			$myerror->error($hr_log_history, 'searchhr&page=1');
		}
	}else{
		//非admin只能删除自己创建的且未审批通过的
		$rs = $mysql->q('select id from hr_log where created_by = ? and id = ? and is_approve = 0', $_SESSION["logininfo"]["aName"], $_GET['delid']);
		if($rs){
			$rs = $mysql->q('delete from hr_log where id = ? and created_by = ? and is_approve = 0', $_GET['delid'], $_SESSION["logininfo"]["aName"]);
			if($rs){
				$sign = true;
				$hr_log_history = 'Delete hr log success! (201301221645)';	
				$myerror->ok($hr_log_history, 'searchhr&page=1');
			}else{
				$hr_log_history = 'Delete hr log failure! (201301221646)';
				$myerror->error($hr_log_history, 'searchhr&page=1');				
			}
		}else{
			$hr_log_history = 'Without Permission To Access! (201301221421)';
			$myerror->error('Without Permission To Access! (201301222255)', 'main');	
		}
	}
	//add tw_admin_hist log
	$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ADMIN_CATG_HR_LOG, $_SESSION["logininfo"]["aName"].' '.$hr_log_history, $sign?ADMIN_ACTION_HR_LOG_SUCCESS:ADMIN_ACTION_HR_LOG_FAILURE, "", "", 0);

}elseif(isset($_GET['approveid']) && isId($_GET['approveid'])){
	if(isSysAdmin()){
		$rtn = $mysql->qone('select type, total_hours, created_by, is_approve from hr_log where id = ?', $_GET['approveid']);
		if($rtn['is_approve'] == 0){
			if($rtn['type'] == 'OT'){
				$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours + ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);
				if($rs){
					$mysql->q('update hr_log set is_approve = 1 where id = ?', $_GET['approveid']);
					$hr_log_history = 'Approve hr ot log success! (201301221442)';
					$myerror->ok($hr_log_history, 'searchhr&page=1');
					$sign = true;
				}else{
					$hr_log_history = 'Approve hr ot log success! (201301221443)';
					$myerror->error($hr_log_history, 'searchhr&page=1');
				}
			}elseif($rtn['type'] == 'LEAVE'){
				$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours - ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);
				if($rs){
					$mysql->q('update hr_log set is_approve = 1 where id = ?', $_GET['approveid']);
					$hr_log_history = 'Approve hr leave log success! (201301221439)';
					$myerror->ok($hr_log_history, 'searchhr&page=1');
					$sign = true;
				}else{
					$hr_log_history = 'Approve hr leave log failure! (201301221440)';
					$myerror->error($hr_log_history, 'searchhr&page=1');
				}
			}else{
				$hr_log_history = 'Type Error! (201301221441)';
				$myerror->error($hr_log_history, 'searchhr&page=1');
			}
		}elseif($rtn['is_approve'] == 1){
			if($rtn['type'] == 'OT'){
				$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours - ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);
				if($rs){
					$mysql->q('update hr_log set is_approve = 0 where id = ?', $_GET['approveid']);
					$hr_log_history = 'Disapprove hr ot log success! (201301222206)';
					$myerror->ok($hr_log_history, 'searchhr&page=1');
					$sign = true;
				}else{
					$hr_log_history = 'Disapprove hr ot log success! (201301222207)';
					$myerror->error($hr_log_history, 'searchhr&page=1');
				}
			}elseif($rtn['type'] == 'LEAVE'){
				$rs = $mysql->q('update tw_admin set AdminTotalHours = AdminTotalHours + ? where AdminName = ?', $rtn['total_hours'], $rtn['created_by']);
				if($rs){
					$mysql->q('update hr_log set is_approve = 0 where id = ?', $_GET['approveid']);
					$hr_log_history = 'Disapprove hr leave log success! (201301222208)';
					$myerror->ok($hr_log_history, 'searchhr&page=1');
					$sign = true;
				}else{
					$hr_log_history = 'Disapprove hr leave log failure! (201301222209)';
					$myerror->error($hr_log_history, 'searchhr&page=1');
				}
			}else{
				$hr_log_history = 'Type Error! (201301222210)';
				$myerror->error($hr_log_history, 'searchhr&page=1');
			}			
		}else{
			$hr_log_history = 'Error! (201301222204)';
			$myerror->error($hr_log_history, 'searchhr&page=1');			
		}
	}else{
		$hr_log_history = 'Without Permission To Access! (201301221428)';
		$myerror->error('Without Permission To Access! (201301222256)', 'main');	
	}
	//add tw_admin_hist log
	$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ADMIN_CATG_HR_LOG, $_SESSION["logininfo"]["aName"].' '.$hr_log_history, $sign?ADMIN_ACTION_HR_LOG_SUCCESS:ADMIN_ACTION_HR_LOG_FAILURE, "", "", 0);
	
}else{
	$goodsForm = new My_Forms();
	$formItems = array(	
			'hr_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_hr_type(1), 'required' => 1),
			'hr_start_date' => array('title' => 'From', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'readonly' => 'readonly'),
			'hr_end_date' => array('title' => 'To', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'readonly' => 'readonly'),
			'hr_remark' => array('title' => 'Reason', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 100, 'rows' => 5, 'addon' => 'style="width:400px"'),
					
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);
	$goodsForm->init($formItems);
	
	
	if(!$myerror->getAny() && $goodsForm->check()){

		if(substr($_POST['hr_start_date'], -5) == '00:00' && substr($_POST['hr_end_date'], -5) == '00:00'){
		
			if(strtotime($_POST['hr_start_date']) < strtotime($_POST['hr_end_date'])){
				//选取的时间跨度不得大于7天
				if(strtotime($_POST['hr_start_date']) + 604800 >= strtotime($_POST['hr_end_date'])){
				
					$type = $_POST['hr_type'];
					$start_date = $_POST['hr_start_date'];
					$end_date = $_POST['hr_end_date'];
					$remark = $_POST['hr_remark'];
					$created_by = $_SESSION["logininfo"]["aName"];
					$in_date = dateMore();
					//获取加班或请假的总小时数，和计算依据
					$hr_arr = check_all_hr_time($type, $start_date, $end_date);
					$total_hours = $hr_arr['num'];
					$log_detail = $hr_arr['log_detail'];

                    //20130922 去掉了小时数不够不能申请请假的限制，让小时数可透支
/*					$part_sign = false;
					if($type == 'LEAVE'){
						$rtn_hr = $mysql->qone('select AdminTotalHours from tw_admin where AdminID = ?', $_SESSION['logininfo']['aID']);
						//计算总请假时间是否够这次请假
						if($rtn_hr['AdminTotalHours'] - $hr_arr['num'] < 0){	
							$hr_log_history = 'Update total hours error(leave)! (201301212310)';
							$myerror->error('Not enough hours', 'managehr');
						}else{
							$part_sign = true;	
						}
					}elseif($type == 'OT'){
						$part_sign = true;	
					}*/
					
					//if($part_sign){
						$result = $mysql->q('insert into hr_log values (NULL, '.moreQm(9).')', $type, $total_hours, $start_date, $end_date, $remark, $created_by, $in_date, 0, $log_detail);
						if($result){

                            //send email to admin
                            require_once(ROOT_DIR.'class/Mail/mail.php');
                            $date_info = array('date' => date('Y-m-d'));
                            //提交的信息
                            $info = $_SESSION["logininfo"]["aName"].' request '.$type.' from '.$start_date.' to '.$end_date.'. Reason:'.$remark;

                            $rtn_setting = $mysql->qone('select email_admin_request_to from setting');

                            send_mail($rtn_setting['email_admin_request_to'], '', 'http://'.$host.'/sys admin request', $info,
                                $date_info);

							$hr_log_history = 'Submit success! '.$type.' '.$hr_arr['num'].' hours! (201301221454)';
							$myerror->ok('Submit success! '.$type.' '.redFont($hr_arr['num']).' hours! (201301221459)', 'searchhr&page=1');
							$sign = true;
						}else{
							$hr_log_history = 'Submit failure! (201301211553)';	
							$myerror->error($hr_log_history, 'managehr');
						}
					//}
				}else{
					$hr_log_history = 'The time span is too long! (201301211516)';	
					$myerror->error($hr_log_history, 'managehr');	
				}
			}else{
				$hr_log_history = 'Start date larger than or equal to end date error! (201301211459)';
				$myerror->error($hr_log_history, 'managehr');	
			}
		}else{
			$hr_log_history = 'Error date format! (201301211444)';
			$myerror->error($hr_log_history, 'managehr');		
		}
		//add tw_admin_hist log
		$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
					, $_SESSION['logininfo']['aID'], $ip_real
					, ADMIN_CATG_HR_LOG, $_SESSION["logininfo"]["aName"].' '.$hr_log_history, $sign?ADMIN_ACTION_HR_LOG_SUCCESS:ADMIN_ACTION_HR_LOG_FAILURE, "", "", 0);
	}
}


if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}else{
	if($myerror->getWarn()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	
	$goodsForm->begin();
?>
	<table width="40%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">OT/Annual Leave</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>information</legend>
            	<div style="color:#F00; margin-left:30px;" align="left">#1 The system will only count in the working hours 9:00am to 1:00pm, 2:00pm to 6:00pm</div>
                <br />
                <div style="color:#F00; margin-left:30px;" align="left">#2 1 days work overtime is eight hours at most</div>
                <br />
                <div style="color:#F00; margin-left:30px;" align="left">#3 提交前，请仔细核查选定加班（OT）或请假（LEAVE）的起始时间和结束时间。选定天和小时后，点ok，才能将所选的时间显示在输入框中。</div>
                <br />
                <div style="color:#F00; margin-left:30px;" align="left">#4 工作时间的设定是周一到周五9:00-18:00。（如：2013.4.29日是法定假期，但却是周一，在9:00-18:00间申请OT，OT小时数是0。又如：2013.4.28是周日，但是是上班时间，在9:00-18:00申请LEAVE，LEAVE小时数也是0。如遇这种情况请到其他日期申请，并在Reason中写实际加班或请假的日期。）</div>
                <br />
                <div style="color:#F00; margin-left:30px;" align="left">#5 2013-10-10起 元旦、劳动节、国庆节这些公众假期OT也能自动计算OT小时数。</div>
                <br />
                <div style="color:#F00; margin-left:30px;" align="left">#6 申請加班只能在正常工作日9:00到18:00以外時間, 每天最多算8小时。</div>
                <br />
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr>
						<td><? $goodsForm->show('hr_type');?></td>
                        <td>&nbsp;</td>
					</tr>
                </table>
                <br />
                <div style="color:#F00; margin-left:30px; font-size: 12px;" align="left">提示：今天是星期<?=$wkday_ar[date('w')][0]?>，是<?=$wkday_ar[date('w')][1]?>。请参考#4选择日期。</div>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tr valign='top'>
						<td><? $goodsForm->show('hr_start_date');?></td>
                        <td><? $goodsForm->show('hr_end_date');?></td>
                    </tr>
                    <tr>
						<td colspan="2"><? $goodsForm->show('hr_remark');?></td>
					</tr>                  
					<tr valign='top'>
						<td colspan='2' height="35">
						<?
						$goodsForm->show('submitbtn');
						?></td>	
					</tr>	
				</table>
			</fieldset>	
			</td>	
		</tr>
	</table>
<?
	$goodsForm->end();
}
?>


<script>
$(function(){
	//因为灰色一般是指不能修改，怕别人误会也不去选择日期了，所以去掉了灰色
	$("#hr_start_date").removeClass("readonly");
	$("#hr_end_date").removeClass("readonly");
	
	$('#hr_start_date').click(function(){WdatePicker({dateFmt:'yyyy-MM-dd HH:00:00',lang:'en',maxDate:'#F{$dp.$D(\'hr_end_date\')}'});});
	$('#hr_end_date').click(function(){WdatePicker({dateFmt:'yyyy-MM-dd HH:00:00',lang:'en',minDate:'#F{$dp.$D(\'hr_start_date\')}'});})
	
})
</script>
