<?

if( isset($_GET['value']) && $_GET['value'] != '' && isset($_GET['value0']) && $_GET['value0'] != ''){
	//中文解碼js ajax傳過來的escape編碼後的中文
	$value = '';
	if($_GET['ajax'] == 'supplier'){
		$value = unescape($_GET['value']);
        $rtn = $mysql->qone('select address, tel1, fax from contact where sid = ? and concat(title, ?, name, ?, family_name) = ?', $_GET['value0'], ' ', ' ', $value);
	}else if($_GET['ajax'] == 'customer'){
        //20130723 也加了unescape
		$value = unescape($_GET['value']);
        $rtn = $mysql->qone('select address, tel1, fax from contact where cid = ? and concat(title, ?, name, ?, family_name) = ?', $_GET['value0'], ' ', ' ', $value);
	}else if($_GET['ajax'] == 'sample_order'){
        $value = unescape($_GET['value']);
        $rtn = $mysql->qone('select t.address, t.tel1, t.fax from contact t, supplier s where t.sid = s.sid and s.name = ? and concat(t.title, ?, t.name, ?, t.family_name) = ?', $_GET['value0'], ' ', ' ', $value);
    }

	if($rtn){
		echo $rtn['tel1'].'|'.$rtn['fax'].'|'.$rtn['address'];
	}else{
		echo 'no-2';
	}
}else{
	echo 'no-1';	
}