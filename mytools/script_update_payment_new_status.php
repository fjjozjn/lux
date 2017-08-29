<?php
//20150120 更新所有payment advice的status

require('../in7/global.php');

$rs = $mysql->q('select py_no, remitting_amount from payment_new order by id desc');
if($rs){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $rtn_item = $mysql->qone('select sum(received) as received_total from payment_item_new where py_no = ?', $v['py_no']);
        $status = '';
        if($v['remitting_amount'] == $rtn_item['received_total']){
            $status = '(C)';
        }else{
            $status = '(I)';
        }
        //echo $v['py_no'].' '.$status.'<br />';
        //die();
        $mysql->q('update payment_new set istatus = ? where py_no = ?', $status, $v['py_no']);
    }
}