<?
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if( isset($_GET['pid']) && $_GET['pid'] != ''){
	$rtn = $mysql->q('select it.pid, i.send_to, i.attention, i.mark_date, it.vid, it.price, it.quantity from invoice i, invoice_item it where i.vid = it.vid and it.pid = ? order by i.mark_date desc', $_GET['pid']);
	if($rtn){
		$result = $mysql->fetch();
		echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<td>Product</td>
					<td>Invoice</td>
					<td>Customer</td>
					<td>Attention</td>
					<td>Price</td>
					<td>Quantity</td>
					<td>Date</td>
				</tr>";
		for($i = 0; $i < count($result); $i++){
			echo '<tr>
					<td>'.$result[$i]['pid'].'</td>
					<td>'.$result[$i]['vid'].'</td>
					<td>'.$result[$i]['send_to'].'</td>
					<td>'.$result[$i]['attention'].'</td>
					<td><font color="#FF0000">'.$result[$i]['price'].'</font></td>
					<td>'.$result[$i]['quantity'].'</td>
					<td>'.$result[$i]['mark_date'].'</td>
				</tr>';
		}
		echo '</table>';
	}else{
		echo 'No Records！';	
	}
}