<?

require('../in7/global.php');

set_time_limit(0);

//PI status 为(S) 的是很检查是否有payment
$rs = $mysql->q('select p.pvid, s.s_date, p.printed_by, p.cid from proforma p, shipment s where p.pvid = s.pi_no and istatus = ?', '(S)');
if($rs){
	$rtn_proforma = $mysql->fetch();
	foreach($rtn_proforma as $v){
        //20140102 不用send_to来找customer，因为有的send_to被改成其他的新地址，导致很多pament_task error 2的错误，现在直接用cid了
		$rtn_customer = $mysql->qone('select balance from customer where cid = ?', $v['cid']);
		if($rtn_customer){
			//customer 的 balance 如果为空，则都为其变量赋默认值0
			if($rtn_customer['balance'] === ''){
				$rtn_customer['balance'] = 0;	
			}
			//时间（秒）：代表shipped后，多久还没有payment
			$over_time = time() - strtotime($v['s_date']);
			//shipment complete 后过了 $rtn_customer['balance'] 天，还没有 payment，则提醒
			if($over_time >= $rtn_customer['balance'] * 24 * 60 * 60){
				$rtn_payment = $mysql->qone('select * from payment where pi_no = ? and p_status = ?', $v['pvid'], 'Balance');
				if(!$rtn_payment){
					$rs_bb = $mysql->q('select * from bulletin_board where content like ? and b_from = ?', '%'.$v['pvid'].' Payment delay%', 'system');
					//如果在 bulletin_board 中已经存在一条system提醒次PI，就更新日期和内容，不存在则插入
					if($rs_bb){
						$mysql->q('update bulletin_board set b_date = ?, content = ? where content like ? and b_from = ?', dateMore(), $v['pvid'].' Payment delay for '.ceil($over_time/(24 * 60 * 60) - $rtn_customer['balance']).' days', '%'.$v['pvid'].' Payment delay%', 'system');
					}else{
						$mysql->q('insert into bulletin_board (b_from, b_to, b_date, content) values ('.moreQm(4).')', 'system', $v['printed_by'], dateMore(), $v['pvid'].' Payment delay for '.ceil($over_time/(24 * 60 * 60) - $rtn_customer['balance']).' days');
					}
				}
			}
		}else{
			$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 0, $v['pvid'].' pament_task error 2', 0, "", "", 0);
		}
	}
}else{
	$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 0, 'payment_task error 1', 0, "", "", 0);
}
