<fieldset style="width:70%">
	<legend class='legend'>Top Customer ( 1.Counted by Invoices; 2.all status except "delete"; 3.currency:USD )</legend>
    	<br />
		<table width="80%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">	
			<tr bgcolor='#EEEEEE'> 
				<th></th>
				<th>Company NO.</th>
				<th>Company Name</th>
                <th>Last Invoice Date</th>
                <th>NO. of Invoice</th>
				<th>Total Amount(USD)</th>
			</tr>
<?
			$i = 1;
			$rs = $mysql->q('select cid, name, total_amount from customer order by total_amount*1 desc limit 50');
			if($rs){
				$rtn = $mysql->fetch();
				foreach($rtn as $v){
					$rs = $mysql->q('select mark_date from invoice where istatus <> ? and cid = ? order by mark_date desc', 'delete', $v['cid']);
					$num = 0;
					if($rs){
						$rtn = $mysql->fetch();
						$num = count($rtn);	
					}
					echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";	
					echo "	<td>".$i."</td>";
					echo "	<td><a href='?act=com-modifycustomer&modid=".$v['cid']."'>".$v['cid']."</a></td>";
					echo "	<td>".$v['name']."</td>";
					echo "	<td>".date('Y-m-d', strtotime($rtn[0]['mark_date']))."</td>";
					echo "	<td>".$num."</td>";
					echo "	<td align='right'>".formatMoney($v['total_amount'])."</td>";
					echo "</tr>";
					$i++;
				}
			}
?>	
        </table>
</fieldset>        