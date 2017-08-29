<?

if(isset($_GET['value'])){
	$ex_factory_cost = 0;
	$rtn_item = $mysql->q('select cost from overheads where po_no = ? and description = ?', $_GET['value'], 'Ex-factory');
	if($rtn_item){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			$ex_factory_cost += $v['cost'];
		}
	}
	
	$total = 0;
	$outstanding_value = 0;
	$rtn_items = $mysql->q('select price, quantity from purchase_item where pcid = ?', $_GET['value']);
	if($rtn_items){
		$rtn = $mysql->fetch();
		foreach($rtn as $v){
			$total += $v['price'] * $v['quantity'];	
		}
		
		$rtn = $mysql->q('select amount from settlement where po_no = ?', $_GET['value']);
		if($rtn){
			$rtn = $mysql->fetch();
			foreach($rtn as $v){
				$outstanding_value += $v['amount'];	
			}
		}
		//20121017 加number_format 不知道为什么这里又出现了科学计算法的多位显示，为什么有多位，我也不知道
		echo number_format(($total + $ex_factory_cost), 2, '.', '') . '|' . number_format(($total + $ex_factory_cost - $outstanding_value), 2, '.', '');
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}