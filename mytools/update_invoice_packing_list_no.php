<?php
require('../in7/global.php');

/*$invoice_packing_list = array();

$rs = $mysql->q('select distinct ref from packing_list_item order by id');
if($rs){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $rs = $mysql->q('select distinct pl_id from packing_list_item where ref = ?', $v['ref']);
        if($rs){
            $rtn_pl_id = $mysql->fetch();
            foreach($rtn_pl_id as $w){
                $invoice_packing_list[$v['ref']][] = $w['pl_id'];
            }
        }
    }
}

foreach($invoice_packing_list as $key=>$z){
    $mysql->q('update invoice set packing_num = ? where vid = ?', implode(',', $z), $key);
}*/

$rs = $mysql->q('select vid from invoice order by vid');
if($rs){
    $rtn_vid = $mysql->fetch();
    foreach($rtn_vid as $v){
        $rs = $mysql->q('select distinct pl_id from packing_list_item where ref = ?', $v['vid']);
        $packing_num = '';
        $temp = array();
        if($rs){
            $rtn_packing_list = $mysql->fetch();
            foreach($rtn_packing_list as $w){
                $temp[] = $w['pl_id'];
            }
            $packing_num = implode(',', $temp);
        }
        $mysql->q('update invoice set packing_num = ? where vid = ?', $packing_num, $v['vid']);
    }
}