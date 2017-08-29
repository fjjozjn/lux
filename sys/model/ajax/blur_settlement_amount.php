<?
if(isset($_GET['po_no']) && isset($_GET['value'])){	
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$old_amount = $mysql->qone('select amount from settlement where id = ?', $_GET['modid']);
		echo $old_amount['amount'];
	}else{
		echo 'yes';	//非mod状态
	}
}else{
	echo 'no-1';	
}