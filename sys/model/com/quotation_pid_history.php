<?
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if( isset($_GET['pid']) && $_GET['pid'] != ''){
	$rtn = $mysql->q('select i.pid, q.send_to, q.attention, q.mark_date, i.qid, i.price, i.quantity from quotation q, quote_item i where q.qid = i.qid and i.pid = ? order by q.mark_date desc', $_GET['pid']);
	if($rtn){
		$result = $mysql->fetch();
		echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<td>Product</td>
					<td>Quotation</td>
					<td>To</td>
					<td>Attention</td>
					<td>Price</td>
					<td>Quantity</td>
					<td>Date</td>
				</tr>";
		for($i = 0; $i < count($result); $i++){
			echo '<tr>
					<td>'.$result[$i]['pid'].'</td>
					<td>'.$result[$i]['qid'].'</td>
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