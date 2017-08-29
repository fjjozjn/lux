<?

require('../in7/global.php');

set_time_limit(0);

$rs = $mysql->q('select pvid, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(expected_date)) as over_time, printed_by from proforma where expected_date < ? and (istatus = ? or istatus = ?)', dateMore(), '(I)', '(P)');
if($rs){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $rs_sd = $mysql->q('select * from bulletin_board where content like ? and b_from = ?', '%'.$v['pvid'].' Shipment delay%', 'system');
        //如果在 bulletin_board 中已经存在一条system提醒的PI shipment delay，就更新日期和内容，不存在则插入
        if($rs_sd){
            $mysql->q('update bulletin_board set b_date = ?, content = ? where content like ? and b_from = ?', dateMore(), $v['pvid'].' Shipment delay for '.ceil($v['over_time']/(24 * 60 * 60)).' days', '%'.$v['pvid'].' Shipment delay%', 'system');
        }else{
            $mysql->q('insert into bulletin_board (b_from, b_to, b_date, content) values ('.moreQm(4).')', 'system', $v['printed_by'], dateMore(), $v['pvid'].' Shipment delay for '.ceil($v['over_time']/(24 * 60 * 60)).' days');
        }
    }
}else{
    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 0, 'shipment_delay error 1', 0, "", "", 0);
}