<?
if( isset($_GET['sign']) && $_GET['sign'] == 'm'){
	$rtn = $mysql->qone('select * from material where m_id = ?', $_GET['value']);
	if($rtn){
		echo $rtn['m_name'] .' '. $rtn['m_type'] .' 单价:'. $rtn['m_price'] .' 单位:'. $rtn['m_unit'];
	}else{
		echo 'no-1';
	}
}elseif( isset($_GET['sign']) && $_GET['sign'] == 't'){
	$rtn = $mysql->qone('select * from task where t_id = ?', $_GET['value']);
	if($rtn){
		echo $rtn['t_name'] .' 工价:'. $rtn['t_price'];
	}else{
		echo 'no-2';
	}
}else{
	echo 'no-3';
}