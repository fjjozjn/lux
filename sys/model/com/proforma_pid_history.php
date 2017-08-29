<?
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if( isset($_GET['pid']) && $_GET['pid'] != ''){
    if(isSysAdmin()){
        $rtn = $mysql->q('select i.pid, p.send_to, p.attention, p.mark_date, i.pvid, i.price, i.quantity from proforma p, proforma_item i where p.pvid = i.pvid and i.pid = ? and istatus <> ? order by p.mark_date desc', $_GET['pid'], 'delete');
    }else{
        $rtn = $mysql->q('select i.pid, p.send_to, p.attention, p.mark_date, i.pvid, i.price, i.quantity from proforma p, proforma_item i where p.pvid = i.pvid and i.pid = ? and istatus <> ? AND printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?) order by p.mark_date desc', $_GET['pid'], 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
    }
	if($rtn){
		$result = $mysql->fetch();
		echo "<table border='1' cellspacing='1' cellpadding='3' align='center'>
				<tr bgcolor='#EEEEEE' align='left'>
					<th>Product</th>
					<th>Proforma</th>
					<th>Customer</th>
					<th>Attention</th>
					<th>Price</th>
					<th>Quantity</th>
					<th>Date</th>
				</tr>";
		for($i = 0; $i < count($result); $i++){
			echo "<tr>
					<td>".$result[$i]['pid']."</td>
					<td>".$result[$i]['pvid']."</td>
					<td>".$result[$i]['send_to']."</td>
					<td>".$result[$i]['attention']."</td>
					<td><font color='#FF0000'>".$result[$i]['price']."</font></td>
					<td>".$result[$i]['quantity']."</td>
					<td>".$result[$i]['mark_date']."</td>
				</tr>";
		}
		echo "</table>";
	}else{
		echo 'No Records！';	
	}
}