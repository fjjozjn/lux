<?php
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
//已移到in7定义
//define("GENERAL_YES", true);
//define("GENERAL_NO", false);
	
/*$type = array(
			  array('项链(N)', '项链(N)'),
			  array('耳环(E)', '耳环(E)'),
			  array('戒子(R)', '戒子(R)'),
			  array('手链(T)', '手链(T)'),
			  array('手镯(H)', '手镯(H)'),
			  array('心针(B)', '心针(B)'),
			  array('套装(S)', '套装(S)'),
			  array('其他(O)', '其他(O)')
			  );
$type_e = array(
				array('Necklace', 'Necklace'),
				array('Earrings', 'Earrings'),
				array('Ring', 'Ring'),
				array('Bracelet', 'Bracelet'),
				array('Bangle', 'Bangle'),
				array('Brooch', 'Brooch'),
				array('Set', 'Set'),
				array('Other', 'Other')				
				);*/
/*$material = array(
				  array('C料', 'C料'),
				  array('A料', 'A料'),
				  array('O号料', 'O号料'),
				  array('锌合金', '锌合金'),
				  array('铜', '铜'),
				  array('银', '银'),
				  array('其他', '其他')
				  );*/

$process = array(
				array('拉粗沙', '拉粗沙'), 
				array('拉幼沙', '拉幼沙'), 
				array('拉沙', '拉沙'), 
				array('油沙', '油沙'), 
				array('闪沙', '闪沙')
				);
$count1 = count($process);
				
$electroplate = array(
					array('氧化银', '氧化银'), 
					array('氧化青铜', '氧化青铜'), 
					array('电白金', '电白金'), 
					array('电白钢', '电白钢'), 
					array('枪色', '枪色'), 
					array('电金', '电金'), 
					array('电银', '电银'), 
					array('电白K', '电白K')
					);
$count2 = count($electroplate);
					
$electroplate_thick = array(
							array('普通电镀', '普通电镀'), 
							array('0.25米', '0.25米'), 
							array('0.5米', '0.5米'), 
							array('1米', '1米'), 
							array('2米', '2米'), 
							array('5米', '5米'), 
							array('10米', '10米')
							);
$count3 = count($electroplate_thick);							

$other = array(
			array('保层', '保层'), 
			array('镭射', '镭射'), 
			array('滴油', '滴油'), 
			array('喷亚克', '喷亚克')
			);	
$count4 = count($other);

$m_type = array(
				array('工件', '工件'),
				array('石料', '石料'),
				array('配件', '配件'),
				array('其他', '其他')
				);

$m_unit = array(
				array('数量', 1),
				array('重量', 2)
				);				

$allinarray = array($process, $electroplate, $electroplate_thick, $other);

$count_array = array($count1, $count2, $count3, $count4);		
$select_max = 0;
for($i = 0; $i < 4; $i++){
	if($select_max < $count_array[$i])
		$select_max = $count_array[$i];		
}

$m_id = array();
$t_id = array();

if($act == 'sendform'){
	$rs = $mysql->q('select id, m_id, m_name from material order by id desc');
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$m_id[] = $v['m_id']; 	
		}
	}
	$rs = $mysql->q('select id, t_id, t_name from task order by id desc');
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$t_id[] = $v['t_id'];
		}
	}
}

//20130731 放到 in7/function.php里去了
/*if( substr($act, 0, 4) == 'com-' && (strpos($act, 'addproduct') || strpos($act, 'modifyproduct') || strpos($act, 'insertproduct') || strpos($act, 'addproforma') || strpos($act, 'modifyproforma') || strpos($act, 'addinvoice') || strpos($act, 'modifyinvoice') || strpos($act, 'addcustomsinvoice') || strpos($act, 'modifycustomsinvoice') || strpos($act, 'addcontact') || strpos($act, 'modifycontact') || strpos($act, 'addsample_order') || strpos($act, 'modifysample_order') || strpos($act, 'insertbom') || strpos($act, 'delivery_to_packing_list') || strpos($act, 'purchase') || strpos($act, 'customer_analysis') || strpos($act, 'credit_note') )){
	if (!isSysAdmin()){
		// mod 4.4 只显示当前用户group创建的customer，因customer在公司内部属于机密内容，涉及员工利益
		// mod 20120723 customer应该是公用的，不能用新的规则
		// 20120723 下午 旧的当group中有多个名字的时候也不适用
		$rs = $mysql->q('select cid, name from customer where created_by in (select AdminName from tw_admin where AdminLuxGroup = (select AdminLuxGroup from tw_admin where AdminName = ?))', $_SESSION['logininfo']['aName']);*/
		//$rs = $mysql->q('select cid, name from customer where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
		/*
		$rtn = $mysql->q('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
		$group_array = explode("\r\n", $rtn['AdminLuxGroup']);
		$all = '';
		foreach($group_array as $v){
			if($v != ''){
				$all .= $v.',';
			}
		}
		$rs = $mysql->q('select cid, name from customer where created_by in (?) or created_by = ?', $all, $_SESSION['logininfo']['aName']);

		if($rs){
			$rows = $mysql->fetch();
			foreach($rows as $v){
				$customer[$v['cid']] = array($v['name'], $v['cid']);
				$customer_so[] = array($v['name'], $v['name']); 	//SO的是要显示给工厂看的，所以是显示工厂全名
			}
			$customer['TEMP'] = array('TEMP', 'TEMP');//这是为了给每个用户都有一个TEMP的customer可用，由于admin已有了，所以这个操作也只是覆盖之前的，不影响
		}
		*/
/*	}else{
		$rs = $mysql->q('select cid, name from customer');
	}
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$customer[$v['cid']] = array($v['name'], $v['cid']);
			$customer_so[] = array($v['name'], $v['name']); 	//SO的是要显示给工厂看的，所以是显示工厂全名
		}
		$customer['TEMP'] = array('TEMP', 'TEMP');//这是为了给每个用户都有一个TEMP的customer可用，由于admin已有了，所以这个操作也只是覆盖之前的，不影响
	}
	//}
}*/

//20170313 product获取$supplier改为放在function里了
if( (substr($act, 0, 4) == 'com-' && (/*strpos($act, 'addproduct') || strpos($act, 'modifyproduct') ||*/ strpos($act, 'insertproduct') || strpos($act, 'addpurchase') || strpos($act, 'modifypurchase') || strpos($act, 'addcontact') || strpos($act, 'modifycontact') || strpos($act, 'addsample_order') || strpos($act, 'modifysample_order') || strpos($act, 'insertbom') || strpos($act, 'factory_analysis'))) || strpos($act, 'fty_manageuser') !== false || strpos($act, 'manageuser') !== false ){
	$rs = $mysql->q('select sid, name from supplier');
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$supplier[$v['sid']] = array($v['name'], $v['sid']); 
			$supplier_so[] = array($v['name'], $v['name']); 
		}
	}
}

if( strpos($act, 'admin_log') !== false || substr($act, 0, 4) == 'bull' || (substr($act, 0, 4) == 'com-' && (/*strpos($act, 'modifyquotation') || strpos($act, 'modifyproforma') ||*/ strpos($act, 'modifyinvoice') || strpos($act, 'modifycustomsinvoice') || strpos($act, 'modifycustomer') || strpos($act, 'modifysample_order') || strpos($act, 'gp') || strpos($act, 'overdue_shipment')))){
	//为了提交表单能统一，所以保留了非管理员的created_by的显示，非管理员只能显示自己的帐号。
	//20130726 只显示 AdminEnabled = 1 的
    if (isSysAdmin()){
		$rs = $mysql->q('select AdminName from tw_admin where AdminName <> ? and AdminEnabled = 1', 'ZJN');
	}else{
		//$rs = $mysql->q('select AdminName from tw_admin where AdminLuxGroup = (select AdminLuxGroup from tw_admin where AdminName = ?)', $_SESSION['logininfo']['aName']);
		$rs = $mysql->q('select AdminName from tw_admin where (AdminLuxGroup like ? OR AdminName = ?) and AdminEnabled = 1', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
	}
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$user[$v['AdminName']] = array($v['AdminName'], $v['AdminName']);	
		}
	}
}

if( substr($act, 0, 4) == 'com-' && strpos($act, 'modifypurchase')){
    //20130726 只显示 AdminEnabled = 1 的
    if (isSysAdmin()){
		$rs = $mysql->q('select AdminNameChi from tw_admin where AdminName <> ? and AdminEnabled = 1', 'ZJN');
	}else{
		//$rs = $mysql->q('select AdminNameChi from tw_admin where AdminLuxGroup = (select AdminLuxGroup from tw_admin where AdminName = ?)', $_SESSION['logininfo']['aName']);
		$rs = $mysql->q('select AdminNameChi from tw_admin where AdminLuxGroup like ? OR AdminName = ? and AdminEnabled = 1', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
	}
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$user[$v['AdminNameChi']] = array($v['AdminNameChi'], $v['AdminNameChi']);	
		}
	}
}

//20130730 获取currency改为放在function里了
/*if( substr($act, 0, 4) == 'com-' && (strpos($act, 'customsinvoice') || strpos($act, 'quotation') || strpos($act, 'proforma') || strpos($act, 'invoice') || strpos($act, 'modifyproduct_e') || strpos($act, 'credit_note') || strpos($act, 'retail_sales_memo') )){
	$rs = $mysql->q('select * from currency');
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$currency[$v['type']] = array($v['type'], $v['type']);	
		}
	}
}*/

$shipment = array(			  
				array('Partial', 'Partial'),
			  	array('Complete', 'Complete')
				);

$payment = array(			  
				array('Deposit', 'Deposit'),
			  	array('Balance', 'Balance')
				);

if( substr($act, 0, 4) == 'com-' && (strpos($act, 'shipment') || strpos($act, 'payment') || strpos($act, 'credit_note'))){
	if (isSysAdmin()){
		$rs = $mysql->q('select pvid from proforma where istatus <> ? order by mark_date desc', 'delete');
	}else{
		//$rs = $mysql->q('select pvid from proforma where pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = ?))) order by mark_date desc', $_SESSION['logininfo']['aName']);
		//$rs = $mysql->q('select pvid from proforma where istatus <> ? and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        //20140325 队员开的单队长看不到，所以改了
		$rs = $mysql->q('select pvid from proforma where istatus <> ? and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
	}
	
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$pvid[$v['pvid']] = array($v['pvid'], $v['pvid']);	
		}
	}else{
		$pvid = '';	
	}
}

$method = array(			  
				array('T/T', 'T/T'),
			  	array('Cash', 'Cash'),
				array('Cheque', 'Cheque'),
				array('Local Bank Transfer', 'Local Bank Transfer'),
				array('Western Union', 'Western Union'),
				array('Other', 'Other')
				);

//20130814 移到in7/function里去了
/*if( substr($act, 0, 4) == 'com-' && (strpos($act, 'overheads') || strpos($act, 'settlement') || strpos($act, 'addqc')) ){
	if (isSysAdmin()){
		if(strpos($act, 'overheads') !== false || strpos($act, 'settlement') !== false){
			$rs = $mysql->q('select pcid from purchase where istatus = ? OR istatus = ? order by mark_date desc', '(I)', '(S)');
		}else{
			$rs = $mysql->q('select pcid from purchase where istatus <> ? order by mark_date desc', 'delete');
		}
	}else{
		if(strpos($act, 'overheads') !== false || strpos($act, 'settlement') !== false){
			$rs = $mysql->q('select pcid from purchase where pcid in (select pcid from purchase where (istatus = ? OR istatus = ?) AND created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup like ? or AdminName = ?)) order by mark_date desc', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
		}else{
			$rs = $mysql->q('select pcid from purchase where istatus <> ? and pcid in (select pcid from purchase where created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup like ? or AdminName = ?)) order by mark_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
		}		
	}
	
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$pcid[$v['pcid']] = array($v['pcid'], $v['pcid']);	
		}
	}else{
		$pcid = '';
	}
}*/

//序号从1开始是A，即出附属单，第2个单就是序号$add[1]就是A，然后继续下去
//mod 2012.8.1 加单直接出A、B、C了，不要加的第一个是和PI编号一样的了
$add = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
//mod 2012.8.3 so需要第一个是空
$add_so = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');


$so_select1 = array('是', '否');
	
$so_select2 = array('12K金', '14K金', '其他');
	
$so_select3 = array('是', '否', '其他');
	
$so_select4 = array('蝴蝶塞', '子弹塞', '飞碟塞', '透明耳塞');

$settlement = array(			  
				array('Deposit', 'Deposit'),
			  	array('Balance', 'Balance')
				);

//改变status状态
define('ADMIN_CHANGE_STATUS', '5');
define('ADMIN_CHANGE_STATUS_SUCCESS', '15');
define('ADMIN_CHANGE_STATUS_FAILURE', '-15');

//管理员可修改PI的状态
$pi_status = array(
				array('Incomplete', '(I)'),
				array('Shipped', '(S)'),
				array('Paid', '(P)'),
				array('Complete', '(C)'),
			);

//20141204 只用在 overdue shipment
$pi_status_overdue_shipment = array(
    array('Oustanding', '(I)|(P)'),
    array('Past', '(S)|(C)'),
);
			
//管理员可修改PO的状态
/*$po_status = array(
				array('Draft', '(D)'),
				array('Incomplete', '(I)'),
				array('Shipped', '(S)'),
				array('Paid', '(P)'),
				array('Complete', '(C)'),
			);*/
						
//title 称呼
/*$title = array(
				array('Mr', 'Mr'),
				array('Ms', 'Ms'),
				array('Mrs', 'Mrs'),
			);*/


//if( $act == 'manageuser'){
//	//group
//	$group = array();
//    //20141027 只选出ERP用户，其他用户不具备group功能
//	$rs = $mysql->q('select AdminName from tw_admin where AdminName <> ? and AdminName <> ? and AdminPlatform like ?', 'ZJN', 'KEVIN', '%sys%');
//	if($rs){
//		$rows = $mysql->fetch();
//		foreach($rows as $v){
//			$group[] = array($v['AdminName'], $v['AdminName']);
//		}
//	}
//}

/*if( $act == 'com-shipment'){
	$courier_or_forwarder = array(
								array('DHL', 'DHL'),
								array('Fedex', 'Fedex'),
								array('UPS', 'UPS'),
								array('Speedmark', 'Speedmark'),
								array('Kesco', 'Kesco'),
								array('Toll Global', 'Toll Global'),
								array('LF Logistic', 'LF Logistic'),
								array('Air City', 'Air City'),
								array('Other', 'Other')
							);
}*/

if( $act == 'com-overheads'){
	$description = array(
						array('Ex-factory', 'Ex-factory'),
						array('FOB HK charge', 'FOB HK charge'),
						array('Packaging', 'Packaging'),
						array('Packaging delivery', 'Packaging delivery'),
						array('Local goods delivery', 'Local goods delivery'),
						array('Material cost', 'Material cost'),
						array('Other, pls specify', 'Other, pls specify')
					);
}

/*if( strpos($act, 'form') !== false ){
	$rs = $mysql->q('select t_id, t_name from task');
	if($rs){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			$t_type[] = array($v['t_id'].' : '.$v['t_name'], $v['t_id']);
		}
	}
}*/

/*if( substr($act, 0, 4) == 'com-' && (strpos($act, 'combine_invoice_to_ci') || strpos($act, 'invoice_to_packing_list') || strpos($act, 'modifypackinglist'))){
	if (isSysAdmin()){
		$rs = $mysql->q('select vid from invoice where istatus <> ? order by mark_date desc', 'delete');
	}else{
		//$rs = $mysql->q('select pvid from proforma where pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = ?))) order by mark_date desc', $_SESSION['logininfo']['aName']);
		//$rs = $mysql->q('select vid from invoice where istatus <> ? and vid in (select vid from invoice where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        //20140325 队员开的单队长看不到，所以改了
		$rs = $mysql->q('select vid from invoice where istatus <> ? and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
	}
	
	if($rs){
		$rows = $mysql->fetch();
		foreach($rows as $v){
			$vid[$v['vid']] = array($v['vid'], $v['vid']);	
		}
	}else{
		$vid = '';	
	}
}*/

if( substr($act, 0, 4) == 'com-' && (strpos($act, 'delivery_to_packing_list') || strpos($act, 'modifypackinglist'))){
    if (isSysAdmin()){
        $rs = $mysql->q('select d_id from delivery order by d_date desc');
    }else{
        $rs = $mysql->q("select d.d_id from delivery d, delivery_item di where d.d_id = di.d_id AND di.po_id in (select pcid from purchase where created_by IN (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?)) GROUP BY d.d_id, di.po_id order by d.d_date desc", '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }
	$did = array();
	if($rs){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			$did[] = array($v['d_id'], $v['d_id']);
		}
	}
}

if( substr($act, 0, 4) == 'com-' && (strpos($act, 'invoice_to_packing_list') || strpos($act, 'delivery_to_packing_list') || strpos($act, 'modifypackinglist'))){
	$rs = $mysql->q('select unit from unit order by quantity');
	$unit = array();
	if($rs){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			$unit[] = array($v['unit'], $v['unit']);
		}
	}
}

//20170313 移到function.php里了
//if( substr($act, 0, 4) == 'com-' && (strpos($act, 'product')) ){
//	$rs = $mysql->q('select id, theme from theme order by id desc');
//	$theme = array();
//	if($rs){
//		$rtn = $mysql->fetch();
//		foreach($rtn as $v){
//			$theme[] = array($v['theme'], $v['id']);
//		}
//	}
//}






define('SYS_SET_TOOL_IP', 1);
define('SYS_SET_MAINTAIN', 2);
$system_setting =  array(
	array(
		'title' => '后台IP限制', 
		'value' =>SYS_SET_TOOL_IP, 		
		'field' => array('可登入IP', 'tools_restrict_ip'), 		
	),
	
);



//20121025 用户登入日志记录
define('ADMIN_CATG_LOGIN', 'login');
define('ADMIN_CATG_LOGOUT', 'logout');
define('ADMIN_ACTION_LOGIN_SUCCESS', '1');
define('ADMIN_ACTION_LOGIN_FAILURE', '-1');
define('ADMIN_ACTION_LOGOUT_SUCCESS', '1');
//登出时只unset session ，但是unset没有返回值，无法判断是否失败，只能认为都是成功的了
//define('ADMIN_ACTION_LOGOUT_FAILURE', '-1');
//20121217 记录用户每时每刻操作系统的记录，VIEW就不需要加失败的值了(因为这两个常量用在global中，但是global又没有引用admin_var.php，所以这个两个常量暂时没有用到)
define('ADMIN_CATG_VIEW', 'view');
define('ADMIN_ACTION_VIEW_SUCCESS', '1');
//20130121
define('ADMIN_CATG_HR_LOG', 'hr_log');
define('ADMIN_ACTION_HR_LOG_SUCCESS', '1');
define('ADMIN_ACTION_HR_LOG_FAILURE', '-1');
//20130124
//20140102 移到in7/var.inc.php 里，此日志能在首页显示
/*define('ADMIN_CATG_SYSTEM_USER', 'admin_info');
define('ADMIN_ADD_SUCCESS', '1');
define('ADMIN_ADD_FAILURE', '-1');
define('ADMIN_MODIFY_SUCCESS', '2');
define('ADMIN_MODIFY_FAILURE', '-2');

define('ADMIN_CATG_FTY_USER', 'fty_user');
define('FTY_USER_ADD_SUCCESS', '1');
define('FTY_USER_ADD_FAILURE', '-1');
define('FTY_USER_MODIFY_SUCCESS', '2');
define('FTY_USER_MODIFY_FAILURE', '-2');*/
//20130702 warehouse
define('WAREHOUSE_LOG_TYPE', 'warehouse');
define('WAREHOUSE_ITEM_INSERT_SUCCESS', '1');
define('WAREHOUSE_ITEM_INSERT_FAILURE', '-1');
define('WAREHOUSE_ITEM_UPDATE_SUCCESS', '2');
define('WAREHOUSE_ITEM_UPDATE_FAILURE', '-2');
define('WAREHOUSE_ITEM_LOG_INSERT_SUCCESS', '3');
define('WAREHOUSE_ITEM_LOG_INSERT_FAILURE', '-3');
define('WAREHOUSE_ITEM_ADD_SUCCESS', '4');
define('WAREHOUSE_ITEM_ADD_FAILURE', '-4');
//20130712 retail sales memo
define('RETAIL_SALES_MEMO_LOG_TYPE', 'retail_sales_memo');
define('RETAIL_SALES_MEMO_ITEM_ADD_SUCCESS', '1');
define('RETAIL_SALES_MEMO_ITEM_ADD_FAILURE', '-1');
define('RETAIL_SALES_MEMO_ITEM_UPDATE_SUCCESS', '2');
define('RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE', '-2');
//20131024
/*define('ADMIN_CATG_LUXCRAFT_USER', 'luxcraft_user');
define('LUXCRAFT_USER_ADD_SUCCESS', '1');
define('LUXCRAFT_USER_ADD_FAILURE', '-1');
define('LUXCRAFT_USER_MODIFY_SUCCESS', '2');
define('LUXCRAFT_USER_MODIFY_FAILURE', '-2');*/




//admin log variable
define('ADMIN_GRP_ADD_SUCCESS', '1000');
define('ADMIN_GRP_ADD_FAILURE', '-1000');
define('ADMIN_GRP_MODIFY_SUCCESS', '1001');
define('ADMIN_GRP_MODIFY_FAILURE', '-1001');
define('PASSWORD_MODIFY_SUCCESS', '1004');
define('PASSWORD_MODIFY_FAILURE', '-1004');
define('SYSTEM_MAINTAIN_SUCCESS', '1007');
define('SYSTEM_MAINTAIN_FAILURE', '-1007');

define('ADMIN_CATG_CHARGE', '4');
define('MYCARDSET_MODIFY_SUCCESS', '4001');
define('MYCARDSET_MODIFY_FAILURE', '-4001');
define('MYCARDSET_ADD_SUCCESS', '4002');
define('MYCARDSET_DEL_SUCCESS', '4003');

//permission setting
define('PERM_ALL', -1);
define('PERM_VIEW_ADMINGRP', 1001);
define('PERM_MANAGE_ADMINGRP', 1002);
define('PERM_VIEW_ADMIN', 1003);
define('PERM_MANAGE_ADMIN', 1004);

define('PERM_ENQ_GAME_ACC', 1010);
define('PERM_MAINTAIN_ONOFF', 1018);

$permission_arr =  array(
	array("全部权限", PERM_ALL),
	array("检视管理员群组", PERM_VIEW_ADMINGRP),
	array("修改管理员群组", PERM_MANAGE_ADMINGRP),
	array("检视管理员", PERM_VIEW_ADMIN),
	array("修改管理员", PERM_MANAGE_ADMIN),

	array("查询游戏帐号", PERM_ENQ_GAME_ACC),
	array("开启关闭系统维护", PERM_MAINTAIN_ONOFF),
);