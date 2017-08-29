<?

if( isset($_GET['value']) && $_GET['value'] != ''){
	$rtn = $mysql->qone('select address from fty_client where company = ?', unescape($_GET['value']));
	if($rtn){
		echo $rtn['address'];
	}else{
		echo 'no-2';	
	}		
}else{
	echo 'no-1';	
}