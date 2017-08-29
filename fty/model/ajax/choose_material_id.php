<?

if(isset($_GET['value'])){
	$rtn = $mysql->qone('select m_color, m_price, m_unit, m_loss from fty_material where m_id = ?', $_GET['value']);
	if($rtn){
		echo $rtn['m_color'].'|'.$rtn['m_price'].'|'.$rtn['m_unit'].'|'.$rtn['m_loss'];
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}