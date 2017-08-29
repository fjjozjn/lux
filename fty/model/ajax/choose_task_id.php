<?

if(isset($_GET['value'])){
	if(isFtyAdmin()){
		$rtn = $mysql->qone('select t_price from fty_task where t_id = ?', $_GET['value']);
	}else{
		//$rtn = $mysql->qone('select t_price from fty_task where t_id = ? and created_by in (select AdminName from tw_admin where FtyName = (select FtyName from tw_admin where AdminName = ?))', $_GET['value'], $_SESSION['ftylogininfo']['aName']);
        //20141202 上面的不知道怎么不行，没解决，暂时用下面的
       // $rtn = $mysql->qone('select t_price from fty_task where t_id = ? and created_by = ?', $_GET['value'], $_SESSION['ftylogininfo']['aName']);
        //20150102 用这个才行，不知道最上面的为什么不行
        $rtn = $mysql->qone('SELECT f.t_price FROM fty_task f, tw_admin a WHERE f.created_by = a.AdminName AND f.t_id = ? AND a.FtyName = (SELECT FtyName FROM tw_admin WHERE AdminName = ?)', $_GET['value'], $_SESSION['ftylogininfo']['aName']);
	}
	if($rtn){
		echo $rtn['t_price'];
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}