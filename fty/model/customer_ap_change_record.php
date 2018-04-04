<?php
/**
 * Author: zhangjn
 * Date: 2018/4/4
 * Time: 17:14
 */
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/fty/in38/global_admin.php');

if( isset($_GET['cid']) && $_GET['cid']){

    $rtn = $mysql->q('select * from tw_admin_hist where AdminHistAction = ? and AdminHistFieldID = ? order by AdminHistID desc', ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS, $_GET['cid']);

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
    }
}