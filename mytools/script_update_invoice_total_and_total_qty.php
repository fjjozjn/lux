<?

require('../in7/global.php');
$rs_v = $mysql->q('select vid from invoice');
$rtn_v = $mysql->fetch();
foreach($rtn_v as $v){
	$rs_items = $mysql->q('select price, quantity from invoice_item where vid = ?', $v['vid']);
	$rtn_items = $mysql->fetch();
	$total = 0;
    $total_qty = 0;
	if($rtn_items){
		foreach($rtn_items as $i){
            $total_qty += $i['quantity'];
			$total += $i['price'] * $i['quantity'];
		}
	}
	$mysql->q('update invoice set total = ?, total_qty = ? where vid = ?', $total, $total_qty, $v['vid']);
}