<?

if(isset($_GET['value'])){
	//$invoice_rtn = $mysql->qone('select send_to, tel, unit, reference from invoice where vid = ?', $_GET['value']);
	//if($invoice_rtn){
		$rs = $mysql->q('select p_id, c_code, quantity, weight, size_l, size_w, size_h, box_num from delivery_item where d_id = ?', $_GET['value']);
		if($rs){
			$rtn = $mysql->fetch();
			$str = '';
			for($i = 0; $i < count($rtn); $i++){
				$str .= ($rtn[$i]['p_id'].'|'.$rtn[$i]['c_code'].'|'.$rtn[$i]['quantity'].'|'.$rtn[$i]['weight'].'|'.$rtn[$i]['size_l'].'|'.$rtn[$i]['size_w'].'|'.$rtn[$i]['size_h'].'|'.$rtn[$i]['box_num'].',');
			}
			echo /*$invoice_rtn['send_to'].'|'.$invoice_rtn['tel'].'|'.$invoice_rtn['unit'].'|'.$invoice_rtn['reference'].*/' @ '.trim($str, ',');
		}else{
			echo '!no-1';	
		}
	//}else{
		//echo '!no-2';
	//}
}else{
	echo '!no-0';
}