<?

if(isset($_GET['value'])){
	$rtn = $mysql->qone('select id from bom where g_id = ?', $_GET['value']);
	if($rtn){
		echo 'no-1';
	}else{
        $rtn = $mysql->qone('select pid from product where pid = ?', $_GET['value']);
        if($rtn){
            echo 'no-2';
        }else{
            echo 'yes';
        }
	}
}else{
	echo 'no-0';
}