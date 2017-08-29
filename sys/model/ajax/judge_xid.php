<?
if( isset($_GET['field']) && $_GET['field'] != ''){
	if( isset($_GET['value']) && $_GET['value'] != ''){
		$db = '';
		$field = '';
		switch($_GET['field']){
			case 'q_qid':
				$db = 'quotation';
				$field = 'qid';
				break;
			case 'pi_pvid':
				$db = 'proforma';
				$field = 'pvid';
				break;
			case 'pc_pcid':
				$db = 'purchase';
				$field = 'pcid';
				break;
			case 'i_vid':
				$db = 'invoice';
				$field = 'vid';
				break;	
			case 'ci_vid':
				$db = 'customs_invoice';
				$field = 'vid';
				break;			
			case 'p_pid':
				$db = 'product';
				$field = 'pid';		
		}
		$rtn = $mysql->qone('select * from '.$db.' where '.$field.' = ?', $_GET['value']);
		if($rtn){
			echo 'no';
		}else{
			echo 'yes';
		}
	}else{
		//出错就不输出了
	}
}else{
	//出错就不输出了
}