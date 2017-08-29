<?
//要写完整的目录，否则linux命令执行此文件会找不到路径
//require('../in7/global.php');
require('/root/Dropbox/luxerp/in7/global.php');

//运行脚本无时间限制
set_time_limit(0);
//先全部清0
$rs = $mysql->q('update product set total_nums = 0, total_amount = 0');
if($rs){
	//非delete status 的单 （或者循环所有product，在invoice_item表里找）
	$rs = $mysql->q('select vid, currency from invoice where istatus <> ?', 'delete');
	if($rs){
		$rtn_invoice = $mysql->fetch();
		foreach($rtn_invoice as $v){
			$rs = $mysql->q('select pid, price, quantity from invoice_item where vid = ?', $v['vid']);
			if($rs){	
				$rtn_item = $mysql->fetch();
				foreach($rtn_item as $w){
					if($v['currency'] == 'USD'){
						$mysql->q('update product set total_amount = total_amount + ?, total_nums = total_nums + ? where pid = ?', $w['price'] * $w['quantity'], $w['quantity'], $w['pid']);
					}else{
						$mysql->q('update product set total_amount = total_amount + ?, total_nums = total_nums + ? where pid = ?', currencyTo($w['price'], $v['currency'], 'USD') * $w['quantity'], $w['quantity'], $w['pid']);
					}
				}
			}
		}
	}
}else{
	die('update table product field total_nums and total_amount to 0 failure !');
}

//各种汇率转换
function currencyTo($num, $c_from, $c_to){
	$rtn_from = mysql_qone('select rate from currency where type = ?', $c_from);
	$rtn_to = mysql_qone('select rate from currency where type = ?', $c_to);
	return ($num / $rtn_from['rate'] * $rtn_to['rate']);
}