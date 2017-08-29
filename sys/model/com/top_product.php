<fieldset style="width:70%">
	<legend class='legend'>Top Product ( 1.Counted by Invoices; 2.all status except "delete"; 3.currency:USD )</legend>
    	<br />
		<table width="60%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">	
			<tr bgcolor='#EEEEEE'> 
				<th></th>
				<th>Product NO.</th>
				<th>Last Invoice Date</th>
                <th>Total Qty</th>
				<th>Total Amount(USD)</th>
			</tr>
<?
			$i = 1;
			$rs = $mysql->q('select pid, total_nums, total_amount from product order by total_amount*1 desc limit 50');
			if($rs){
				$rtn = $mysql->fetch();
				foreach($rtn as $v){
					$rtn_date = $mysql->qone('select i.mark_date from invoice i, invoice_item it where i.vid = it.vid and i.istatus <> ? and it.pid = ? order by i.mark_date desc limit 1', 'delete', $v['pid']);
					echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";	
					echo "	<td>".$i."</td>";
					echo "	<td><a href='?act=com-modifyproduct&modid=".$v['pid']."'>".$v['pid']."</a></td>";
					echo "	<td>".date('Y-m-d', strtotime($rtn_date['mark_date']))."</td>";
					echo "  <td>".$v['total_nums']."</td>";
					echo "	<td align='right'>".formatMoney($v['total_amount'])."</td>";
					echo "</tr>";
					$i++;
				}
			}
?>	
        </table>
</fieldset>        