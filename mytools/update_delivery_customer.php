<?php

//20141222 把delivery_item表里的sid字段更新为客户号

require('../in7/global.php');

$delivery_item_rs = $mysql->q('select po_id from delivery_item where sid like ? group by po_id', '%S0%');

if($delivery_item_rs){
    $delivery_item_rtn = $mysql->fetch();

    foreach($delivery_item_rtn as $v){
        $mysql->q('update delivery_item set sid = (select customer from purchase where pcid = ?) where po_id = ?', $v['po_id'], $v['po_id']);
    }
}