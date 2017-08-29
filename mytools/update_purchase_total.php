<?

require('../in7/global.php');
$rs_p = $mysql->q('select pcid from purchase');
$rtn_p = $mysql->fetch();
foreach($rtn_p as $v){
	$rs_items = $mysql->q('select price, quantity from purchase_item where pcid = ?', $v['pcid']);
	$rtn_items = $mysql->fetch();
	$total = 0;
	if($rtn_items){
		foreach($rtn_items as $i){
			$total += $i['price'] * $i['quantity'];
		}
	}
	$mysql->q('update purchase set total = ? where pcid = ?', $total, $v['pcid']);	
}