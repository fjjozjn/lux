<?

if(isset($_GET['value'])){
	$rtn = $mysql->qone('select m_color, m_price, m_unit from material where m_id = ?', $_GET['value']);
	if($rtn){
		echo $rtn['m_color'].'|'.$rtn['m_price'].'|'.$rtn['m_unit'];
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}