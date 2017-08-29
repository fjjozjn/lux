<?
//要写完整的目录，否则linux命令执行此文件会找不到路径
//require('../in7/global.php');
//require('/root/Dropbox/luxerp/in7/global.php');
require(substr(__DIR__, 0, -7).'in7/global.php');

//20130407 运行脚本前先把customer 的 total_amount 值清0
$rs = $mysql->q('update customer set total_amount = 0');
if($rs){
	$rs = $mysql->q('select vid, currency, cid from invoice where istatus <> ?', 'delete');
	if($rs){
		$rtn_invoice = $mysql->fetch();	
		foreach($rtn_invoice as $v){
			$customer_total_amount = 0;
			$rs = $mysql->q('select price, quantity from invoice_item where vid = ?', $v['vid']);
			if($rs){
				$rtn_item = $mysql->fetch();
				foreach($rtn_item as $w)
					if($v['currency'] == 'USD'){
						$customer_total_amount += $w['price'] * $w['quantity'];
					}else{
						$customer_total_amount += currencyTo($w['price'], $v['currency'], 'USD') * $w['quantity'];
					}
			}
			$mysql->q('update customer set total_amount = total_amount + ? where cid = ?', $customer_total_amount, $v['cid']);
		}
	}
}else{
	die('update table customer field total_amount to 0 failure !');	
}

//各种汇率转换
function currencyTo($num, $c_from, $c_to){
	$rtn_from = mysql_qone('select rate from currency where type = ?', $c_from);
	$rtn_to = mysql_qone('select rate from currency where type = ?', $c_to);
	return ($num / $rtn_from['rate'] * $rtn_to['rate']);
}