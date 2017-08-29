<?
//只是判斷在proforma或invoice的item裏是否有pid
if( isset($_GET['ajax']) && $_GET['ajax'] == 'proforma'){
	if( isset($_GET['pid']) && $_GET['pid'] != ''){
		$rtn = $mysql->qone('select pid from proforma_item where pid = ?', $_GET['pid']);
		if(!$rtn){
			echo 'no-2';	
		}
	}else{
		echo 'no-1';	
	}
}elseif( isset($_GET['ajax']) && $_GET['ajax'] == 'invoice'){
	if( isset($_GET['pid']) && $_GET['pid'] != ''){
		$rtn = $mysql->qone('select pid from invoice_item where pid = ?', $_GET['pid']);
		if(!$rtn){
			echo 'no-4';	
		}
	}else{
		echo 'no-3';	
	}	
}elseif( isset($_GET['ajax']) && $_GET['ajax'] == 'quotation'){
	if( isset($_GET['pid']) && $_GET['pid'] != ''){
		$rtn = $mysql->qone('select pid from quote_item where pid = ?', $_GET['pid']);
		if(!$rtn){
			echo 'no-6';	
		}
	}else{
		echo 'no-5';	
	}	
}elseif( isset($_GET['ajax']) && $_GET['ajax'] == 'purchase'){
	if( isset($_GET['pid']) && $_GET['pid'] != ''){
		$rtn = $mysql->qone('select pid from purchase_item where pid = ?', $_GET['pid']);
		if(!$rtn){
			echo 'no-8';	
		}
	}else{
		echo 'no-7';	
	}	
}else{
	echo 'no-0';	
}