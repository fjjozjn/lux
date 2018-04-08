<?php
/**
 * Author: zhangjn
 * Date: 2018/4/4
 * Time: 17:14
 */
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/fty/in38/global_admin.php');

if( isset($_GET['cid']) && $_GET['cid']){

    /*$rtn = $mysql->q('select * from tw_admin_hist where AdminHistAction = ? and AdminHistField = ? order by AdminHistID desc', ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS, $_GET['cid']);

    if($rtn){
        $result = $mysql->fetch();
        echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<th>History</th>
				</tr>";
        for($i = 0; $i < count($result); $i++){
            echo "<tr>
					<td>".$result[$i]['AdminHistRemark']."</td>
				</tr>";
        }
        echo "</table>";
    }else{
        echo 'No Records！';
    }*/

    $rtn = $mysql->q('select * from fty_payment_request_item where fty_customer like ? and is_paydone = 1 order by id desc', $_GET['cid'].'%');

    if($rtn){
        $result = $mysql->fetch();
        echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<th>付款申请单ID</th>
					<th>付款日期</th>
					<th>付款金额</th>
				</tr>";
        for($i = 0; $i < count($result); $i++){
            echo "<tr>
					<td>".$result[$i]['main_id']."</td>
					<td>".$result[$i]['paydone_date']."</td>
					<td>".$result[$i]['actual_pay_amount']."</td>
				</tr>";
        }
        echo "</table>";
    }else{
        echo 'No Records！';
    }
}