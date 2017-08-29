<?

if( isset($_GET['value']) && $_GET['value'] != '' && isset($_GET['value0']) && $_GET['value0'] != ''){
	//中文解碼js ajax傳過來的escape編碼後的中文
	$value = '';
	if($_GET['ajax'] == 'wlgy_'){
		$value = unescape($_GET['value']);
        $rtn = $mysql->qone('select address, tel1, fax from fty_wlgy_contact where cid = ? and concat(title, ?, name, ?, family_name) = ?', $_GET['value0'], ' ', ' ', $value);
	}else if($_GET['ajax'] == 'jg_'){
        $value = unescape($_GET['value']);
        $rtn = $mysql->qone('select address, tel1, fax from fty_jg_contact where cid = ? and concat(title, ?, name, ?, family_name) = ?', $_GET['value0'], ' ', ' ', $value);
	}

	if($rtn){
		echo $rtn['tel1'].'|'.$rtn['fax'].'|'.$rtn['address'];
	}else{
		echo 'no-2';
	}
}else{
	echo 'no-1';	
}