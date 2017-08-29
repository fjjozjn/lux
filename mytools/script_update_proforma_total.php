<?php
require('../in7/global.php');

//20141216 如果 currency 不是 USD，则按 currency 表的汇率改为USD保存进 proforma total

$currency = get_currency();

$rs_v = $mysql->q('select pvid, currency from proforma');
$rtn_v = $mysql->fetch();
foreach($rtn_v as $v){
    $rs_items = $mysql->q('select price, quantity from proforma_item where pvid = ?', $v['pvid']);
    $rtn_items = $mysql->fetch();
    $total = 0;
    if($rtn_items){
        foreach($rtn_items as $i){
            $total += ($i['price']/$currency[$v['currency']]*$currency['USD']) * $i['quantity'];
        }
    }
    $mysql->q('update proforma set total = ? where pvid = ?', $total, $v['pvid']);
    echo $v['pvid'].' '.$total.'<br />';
}