<?
if(isset($_GET['value'])){
	//找出供应商ID
	$rtn_supplier = $mysql->qone('select cid from proforma where pvid = (select reference from purchase where pcid = ?)', $_GET['value']);
	if($rtn_supplier){
		//找出purchase_item信息	
		$rs = $mysql->q('select pid, price, quantity, ccode from purchase_item where pcid = ?', $_GET['value']);
		if($rs){
			$rtn = $mysql->fetch();
			$str = '';
			for($i = 0; $i < count($rtn); $i++){
				$str .= ($i == count($rtn) - 1) ? ($_GET['value'].'|'.$rtn_supplier['cid'].'|'.$rtn[$i]['pid'].'|'.$rtn[$i]['ccode'].'|'.$rtn[$i]['quantity'].'|'.$rtn[$i]['price']) : ($_GET['value'].'|'.$rtn_supplier['cid'].'|'.$rtn[$i]['pid'].'|'.$rtn[$i]['ccode'].'|'.$rtn[$i]['quantity'].'|'.$rtn[$i]['price'].',');
			}
			echo $str;
		}else{
			echo '!no-1';
		}
	}else{
		echo '!no-3';
	}
}else{
	echo '!no-2';
}