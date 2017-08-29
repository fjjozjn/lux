<?php
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if ((strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])) || (strlen(@$_SESSION['search_criteria']['shipment_start_date']) && strlen(@$_SESSION['search_criteria']['shipment_end_date'])) || (strlen(@$_SESSION['search_criteria']['etd_start']) && strlen(@$_SESSION['search_criteria']['etd_end']))){
	//$sql = 'select pvid, mark_date, printed_by, send_to, reference, istatus from proforma where mark_date between ? and ?';
	$temp_table = ' proforma f';
	
	$where_sql = ' AND f.expected_date <= now()';
	if (strlen(@$_SESSION['search_criteria']['start_date'])){
		if (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND f.mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}else{
			$where_sql.= " AND f.mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
		}
	}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
		$where_sql.= " AND f.mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
	}	
	/*
	if (strlen(@$_SESSION['search_criteria']['user'])){
		$where_sql.= " AND f.printed_by Like '%".$_SESSION['search_criteria']['user'].'%\'';
	}
	*/	
	if (strlen(@$_SESSION['search_criteria']['status'])){
		$where_sql.= " AND f.istatus Like '%".$_SESSION['search_criteria']['status'].'%\'';
	}
	
	if (strlen(@$_SESSION['search_criteria']['shipment_start_date'])){			
		$temp_table .= ' ,shipment s';
		if (strlen(@$_SESSION['search_criteria']['shipment_end_date'])){
			$where_sql.= " AND f.pvid = s.pi_no AND s.s_status = 'Complete' AND s.s_date between '".$_SESSION['search_criteria']['shipment_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['shipment_end_date']." 23:59:59'";
		}else{
			$where_sql.= " AND f.pvid = s.pi_no AND s.s_status = 'Complete' AND s.s_date > '".$_SESSION['search_criteria']['shipment_start_date']." 00:00:00'";
		}
	}elseif (strlen(@$_SESSION['search_criteria']['shipment_end_date'])){
		$where_sql.= " AND f.pvid = s.pi_no AND s.s_status = 'Complete' AND s.s_date < '".$_SESSION['search_criteria']['shipment_end_date']." 23:59:59'";
	}
	
	if (strlen(@$_SESSION['search_criteria']['etd_start'])){			
		if (strlen(@$_SESSION['search_criteria']['etd_end'])){
			$where_sql.= " AND expected_date between '".$_SESSION['search_criteria']['etd_start']." 00:00:00' AND '".$_SESSION['search_criteria']['etd_end']." 23:59:59'";
		}else{
			$where_sql.= " AND expected_date > '".$_SESSION['search_criteria']['etd_start']." 00:00:00'";
		}
	}elseif (strlen(@$_SESSION['search_criteria']['etd_end'])){
		$where_sql.= " AND expected_date < '".$_SESSION['search_criteria']['etd_end']." 23:59:59'";
	}		
	
	//普通用户只能搜索到自己开的单
	if (!isSysAdmin()){
		$where_sql .= " AND printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\'))';
	}
	
	$where_sql.= ' ORDER BY mark_date';	
	
	$list_field = ' f.pvid, f.mark_date, f.printed_by, f.cid, f.reference, f.istatus, f.expected_date ';
	$start_row = 0;
	//默认值100000，相当于一页显示无限多条的记录了
	$end_row = 100000;
	
	$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
	//$rs = $mysql->q($sql, $_SESSION['search_criteria']['start_date']." 00:00:00", $_SESSION['search_criteria']['end_date']." 23:59:59", (strlen(@$_SESSION['search_criteria']['user']))?'%'.$_SESSION['search_criteria']['user'].'%':'%'.$_SESSION['search_criteria']['status'].'%', '%'.$_SESSION['search_criteria']['status'].'%');
	if($info){
		//$rtn = $mysql->fetch();
		$rtn = $mysql->fetch(0, 1);
		header('Content-type: application/vnd.ms-excel; charset=UTF-8');
		header('Content-Disposition: filename=overdue_shipment.xls');
?>

<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<body>
<h1 align="center">OVERDUE SHIPMENT</h1>
	<table width="1000" border='1'>	
		<tr bgcolor='#EEEEEE'>
			<td>&nbsp;</td>
			<td colspan="8" align="center">SALES</td>
			<td colspan="3"></td>
		</tr>
		<tr bgcolor='#EEEEEE'> 
			<th></th>
			<th>PO REC DATE</th>
			<th>STAFF</th>
			<th>C'ID</th>
			<th>CUST' PO#</th>
			<th>PI #</th>
			<th>TOTAL PI AMOUNT(USD)</th>
			<th>INVOICE #</th>
			<th>TOTAL ORDER AMOUNT(USD)</th>
            <th>SHIP DATE</th>
			<th>ETD</th>
			<th>#of days</th>
		</tr>
<?
		$total_pi = 0;
		$total_oa = 0;
		$now = time();
		foreach($rtn as $v){
			echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
			
			$customer = $v['cid'];
			
			//sales
			//proforma 的总和
			$proforma_total = 0;
			//invoice 的总和
			$sales_total = 0;
			
			$invoice_all = '';
							
			//proforma
			$rs_proforma = $mysql->q('select pvid from proforma where pvid like ?', '%'.$v['pvid'].'%');
			if($rs_proforma){
				$rtn_proforma = $mysql->fetch();
				foreach($rtn_proforma as $w){
					$rs_proforma_item = $mysql->q('select price, quantity from proforma_item where pvid = ?', $w['pvid']);
					if($rs_proforma_item){
						$rtn_proforma_item = $mysql->fetch();
						foreach($rtn_proforma_item as $x){
							$proforma_total += $x['price'] * $x['quantity'];
						}
					}						
				}
			}
			$total_pi += $proforma_total;
			
			//invoice
			$rs_invoice = $mysql->q('select vid from invoice where istatus <> ? and vid like ?', 'delete', '%'.$v['pvid'].'%');
			if($rs_invoice){
				$rtn_invoice = $mysql->fetch();
				foreach($rtn_invoice as $w){
					$invoice_all .= /*"<a href='?act=com-modifyinvoice&modid=".$w['vid']."'>".*/$w['vid'].'<br />'/*."</a><br />"*/;
					$rs_invoice_item = $mysql->q('select price, quantity from invoice_item where vid = ?', $w['vid']);
					if($rs_invoice_item){
						$rtn_invoice_item = $mysql->fetch();
						foreach($rtn_invoice_item as $x){
							$sales_total += $x['price'] * $x['quantity'];
						}
					}
				}
			}
			$total_oa += $sales_total;
			
			$rtn_shipment_date = $mysql->qone('select s_date from shipment where pi_no like ? and s_status = ? order by s_date desc', '%'.$v['pvid'].'%', 'Complete');
			
			$of_day = '';
			if($v['expected_date'] != ''){
				if($v['istatus'] == '(S)' || $v['istatus'] == '(C)'){
					$balance = 0;
					$rtn_customer = $mysql->qone('select balance from customer where cid = ?', $customer);
					if($rtn_customer['balance'] != ''){
						$balance = $rtn_customer['balance'];
					}
					
					$s_time = strtotime($rtn_shipment_date['s_date']);
					if($s_time - $balance * 24 * 60 * 60 > strtotime($v['expected_date']) ){
						$of_day = datediff('day', strtotime($v['expected_date']), $s_time - $balance * 24 * 60 * 60);	
					}
				}elseif($v['istatus'] == '(I)' || $v['istatus'] == '(P)'){
					$e_time = strtotime($v['expected_date']);			
					if($e_time < $now){
						$of_day = datediff('day', $e_time, $now);	
					}
				}
			}
			
			echo "	<td>".$v['istatus']."</td>";
			echo "	<td>".(($v['mark_date'] == '') ? '' : date('Y-m-d', strtotime($v['mark_date'])))."</td>";
			echo "	<td>".$v['printed_by']."</td>";
			echo "	<td>".$customer."</td>";
			echo "	<td>".$v['reference']."</td>";
			//echo "	<td><a href='?act=com-modifyproforma&modid=".$v['pvid']."'>".$v['pvid']."</a></td>";
			echo "	<td>".$v['pvid']."</td>";
			echo "  <td>".formatMoney($proforma_total)."</td>";
			echo "	<td align='left'>".$invoice_all."</td>";
			echo "	<td>".formatMoney($sales_total)."</td>";
			echo "	<td>".(($rtn_shipment_date['s_date'] == '') ? '' : date('Y-m-d', strtotime($rtn_shipment_date['s_date'])))."</td>";
			echo "	<td>".(($v['expected_date'] == '') ? '' : date('Y-m-d', strtotime($v['expected_date'])))."</td>";
			echo "	<td>".$of_day."</td>";
			
			echo "</tr>";
		}
		echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'><td colspan='6' align='right'>Total：</td><td>".formatMoney($total_pi)."</td><td align='right'>Total：</td><td>".formatMoney($total_oa)."</td><td></td><td></td><td></td></tr>";
	}
}else{
	die('error!');	
}