<?
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if( isset($_GET['pid']) && $_GET['pid'] != ''){
	$rtn = $mysql->q('select i.pid, p.send_to, p.attention, p.mark_date, i.pcid, i.price, i.quantity from purchase p, purchase_item i where p.pcid = i.pcid and i.pid = ? order by p.mark_date desc', $_GET['pid']);
	if($rtn){
		$result = $mysql->fetch();
		echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<td>Product</td>
					<td>Purchase</td>
					<td>Customer</td>
					<td>Attention</td>
					<td>Price</td>
					<td>Quantity</td>
					<td>Date</td>
				</tr>";
		for($i = 0; $i < count($result); $i++){
			echo '<tr>
					<td>'.$result[$i]['pid'].'</td>
					<td>'.$result[$i]['pcid'].'</td>
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