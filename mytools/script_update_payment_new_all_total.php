<?

require('../in7/global.php');

$rs = $mysql->q('select py_no from payment_new order by in_date desc');
if($rs){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $rs = $mysql->q('select total from payment_item_new where py_no = ?', $v['py_no']);
        if($rs){
            $rtn_item = $mysql->fetch();
            $all_total = 0;
            foreach($rtn_item as $w){
                $all_total += $w['total'];
            }
            $mysql->q('update payment_new set all_total = ? where py_no = ?', my_formatMoney($all_total), $v['py_no']);
        }
    }
}

function my_formatMoney($money){
    return sprintf("%01.2f", round(floatval($money), 2));
}