<?
if( isset($_GET['value']) && $_GET['value'] != ''){
	
	if(isset($_GET['pid_all']) && isset($_GET['quantity_all'])){
        //20130705 不再使用 currency 的session 了，改为直接jquery取页面selectbox的值
		//$_SESSION['currency'] = $_GET['value'];
		//$myerror->info($_SESSION['currency']);
		
		$rtn_markup = $mysql->qone('select markup from setting limit 1');
		$rate = $mysql->qone('select rate from currency where type = ?', $_GET['value']);
		$pid_set = explode('|', $_GET['pid_all']);
		//$myerror->info($pid_set);
		$quantity_set = explode('|', $_GET['quantity_all']);
		//$myerror->info($quantity_set);
		
		$cost_rmb_all = '';
		$total_all = '';
		for($i = 0; $i < count($pid_set); $i++){
			//这里少了乘了个markup，他们也没提出这个错误，所以现在忙，暂时不改，以后再改了！！！！！！！
			$rtn = $mysql->qone('select cost_rmb, exclusive_to from product where pid = ?', $pid_set[$i]);
			//如果有exclusive_to，則使用customer的markup值
			if( $rtn['exclusive_to'] != ''){
				$markup_rtn = $mysql->qone('select markup_ratio from customer where cid = ?', $rtn['exclusive_to']);	
			}
			$markup = (isset($markup_rtn['markup_ratio']) && $markup_rtn['markup_ratio'] != '')?$markup_rtn['markup_ratio']:$rtn_markup['markup'];			
			
			($i == (count($pid_set) - 1))?($cost_rmb_all .= formatMoney($rtn['cost_rmb'] * $markup * $rate['rate'])):($cost_rmb_all .= (formatMoney($rtn['cost_rmb'] * $markup * $rate['rate']) . '|'));
			($i == (count($pid_set) - 1))?($total_all .= (formatMoney($rtn['cost_rmb'] * $quantity_set[$i] * $markup * $rate['rate']))):($total_all .= ((formatMoney($rtn['cost_rmb'] * $quantity_set[$i] * $markup * $rate['rate'])) . '|'));
		}
		echo $cost_rmb_all . ' ' . $total_all;
		//$myerror->info($cost_rmb_all . ' ' . $total_all);
	}elseif(isset($_GET['pid']) && $_GET['pid'] != ''){
		//展会的product选择currency
		$rtn_pid = $mysql->qone('select cost_rmb from product where pid = ?', $_GET['pid']);
		$rtn_markup = $mysql->qone('select markup from setting limit 1');
		$rtn_rate = $mysql->qone('select rate from currency where type = ?', $_GET['value']);
		echo formatMoney($rtn_pid['cost_rmb'] * $rtn_markup['markup'] * $rtn_rate['rate']);
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no';	
}

