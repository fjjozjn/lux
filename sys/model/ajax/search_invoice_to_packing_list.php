<?

if(isset($_GET['value'])){
	$invoice_rtn = $mysql->qone('select send_to, tel, unit, reference from invoice where vid = ?', $_GET['value']);
	if($invoice_rtn){
		$rs = $mysql->q('select pid, ccode, quantity from invoice_item where vid = ? and pid not like ? and pid not like ?', $_GET['value'], 'TEMP%', 'temp%');
		if($rs){
			$rtn = $mysql->fetch();
			$str = '';
			for($i = 0; $i < count($rtn); $i++){
				$str .= ($i == count($rtn) - 1) ? ($rtn[$i]['pid'].'|'.$rtn[$i]['ccode'].'|'.$rtn[$i]['quantity']) : ($rtn[$i]['pid'].'|'.$rtn[$i]['ccode'].'|'.$rtn[$i]['quantity'].',');
			}
			echo $invoice_rtn['send_to'].'|'.$invoice_rtn['tel'].'|'.$invoice_rtn['unit'].'|'.$invoice_rtn['reference'].' @ '.$str;
		}else{
			echo '!no-1';	
		}
	}else{
		echo '!no-2';
	}
}else{
	echo '!no-0';
}