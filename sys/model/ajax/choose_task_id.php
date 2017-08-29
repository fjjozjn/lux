<?

if(isset($_GET['value'])){
	$rtn = $mysql->qone('select t_price, t_time from task where t_id = ?', $_GET['value']);
	if($rtn){
		echo $rtn['t_price'].'|'.$rtn['t_time'].'|'.$rtn['t_price']*$rtn['t_time'];
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}