<?

require('../in7/global.php');

$rs = $mysql->q('select pid from product order by in_date desc');
if($rs){
	$rtn = $mysql->fetch();

	foreach($rtn as $v){
		$total_nums = 0;
		$total_amount = 0;
	
		$rs = $mysql->q('select quantity, price from invoice_item where pid = ?', $v['pid']);
		if($rs){
			$rtn = $mysql->fetch();
			foreach($rtn as $w){
				$total_nums += $w['quantity'];
				$total_amount += ($w['price']/0.1619)*$w['quantity'];
			}
			$mysql->q('update product set total_nums = ?, total_amount = ? where pid = ?', $total_nums, $total_amount, $v['pid']);
		}
	}
}

