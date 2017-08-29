<?

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}

if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	//引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	
	// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
	}
	
	$form = new My_Forms();
	$formItems = array(
			'user' => array(
				'type' => 'select',
				'options' => $user,
				'value' => @$_SESSION['search_criteria']['user'], 
				),			
			'status' => array(
				'type' => 'select',
				'options' => $pi_status, 
				'value' => @$_SESSION['search_criteria']['status'], 
				),
			'start_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				//'required' => 1,
				//'nostar' => true,
				'value' => @$_SESSION['search_criteria']['start_date'], 
				),	
			'end_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				//'required' => 1,
				//'nostar' => true,
				'value' => @$_SESSION['search_criteria']['end_date'], 
				),
			'shipment_start_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['shipment_start_date'], 
				),	
			'shipment_end_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['shipment_end_date'], 
				),
			'payment_start_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['payment_start_date'], 
				),	
			'payment_end_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['payment_end_date'], 
				),	
			'etd_start' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['etd_start'], 
				),	
			'etd_end' => array(
				'type' => 'text', 
				'restrict' => 'date',
				'value' => @$_SESSION['search_criteria']['etd_end'], 
				),								
			'submitbutton' => array(
				'type' => 'submit', 
				'value' => 'Search', 
				'title' => ''),	
			);
	$form->init($formItems);
	$form->begin();	
	
	?>

<table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">GROSS PROFIT</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td height="35" align="right">User : </td>
				<td align="left"><? $form->show('user'); ?></td>
				<td align="right">Status : </td>
				<td align="left"><? $form->show('status'); ?></td>
			</tr>	
			<tr>
				<td align="right">Created Date Start :<h6 class="required"></h6></td>
				<td align="left"><? $form->show('start_date'); ?></td>
                <td align="right">Created Date End :<h6 class="required"></h6></td>
				<td align="left"><? $form->show('end_date'); ?></td>
			</tr>
			<tr>
				<td align="right">Ship Date Start : </td>
				<td align="left"><? $form->show('shipment_start_date'); ?></td>
                <td align="right">Ship Date End : </td>
				<td align="left"><? $form->show('shipment_end_date'); ?></td>
			</tr>	
			<tr>
				<td align="right">Pay Date Start : </td>
				<td align="left"><? $form->show('payment_start_date'); ?></td>
                <td align="right">Pay Date End : </td>
				<td align="left"><? $form->show('payment_end_date'); ?></td>
			</tr>
			<tr>
				<td align="right">ETD Start : </td>
				<td align="left"><? $form->show('etd_start'); ?></td>
                <td align="right">ETD End : </td>
				<td align="left"><? $form->show('etd_end'); ?></td>
			</tr>                        						
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');
				// $form->show('resetbutton');
				
				?><div style="padding-top: 9px;">&nbsp;&nbsp;<a class="button" href="model/com/gp_pdf.php" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>&nbsp;&nbsp;<a class="button" href="model/com/gp_excel.php" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a></div>
                </td>
			</tr>
            <tr><td colspan="4" style="color:#F00" align="center">#请填写查询的日期范围，至少填一组。</td></tr>
		</table>
	</fieldset>	
	</td>	
	</tr>
</table><br />
<?
	$form->end();

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr valign='top'>
	<td align="center" width="80%">	

<?	
if($form->check()){
	if ((strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])) || (strlen(@$_SESSION['search_criteria']['shipment_start_date']) && strlen(@$_SESSION['search_criteria']['shipment_end_date'])) || (strlen(@$_SESSION['search_criteria']['payment_start_date']) && strlen(@$_SESSION['search_criteria']['payment_end_date'])) || (strlen(@$_SESSION['search_criteria']['etd_start']) && strlen(@$_SESSION['search_criteria']['etd_end']))){
		//$sql = 'select pvid, mark_date, printed_by, send_to, reference, istatus from proforma where mark_date between ? and ?';
		$temp_table = ' proforma f';
		
		$where_sql = ' AND f.istatus <> "delete"';
		if (strlen(@$_SESSION['search_criteria']['start_date'])){
			if (strlen(@$_SESSION['search_criteria']['end_date'])){
				$where_sql.= " AND f.mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND f.mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
			$where_sql.= " AND f.mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
		}	
		
		if (strlen(@$_SESSION['search_criteria']['user'])){
			$where_sql.= " AND f.printed_by Like '%".$_SESSION['search_criteria']['user'].'%\'';
		}	
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
		
		if (strlen(@$_SESSION['search_criteria']['payment_start_date'])){	
			$temp_table .= ' ,payment p';
			if (strlen(@$_SESSION['search_criteria']['payment_end_date'])){
				$where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.p_date between '".$_SESSION['search_criteria']['payment_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['payment_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.p_date > '".$_SESSION['search_criteria']['payment_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['payment_end_date'])){
			$where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.p_date < '".$_SESSION['search_criteria']['payment_end_date']." 23:59:59'";
		}

		if (strlen(@$_SESSION['search_criteria']['etd_start'])){			
			if (strlen(@$_SESSION['search_criteria']['etd_end'])){
				$where_sql.= " AND f.expected_date between '".$_SESSION['search_criteria']['etd_start']." 00:00:00' AND '".$_SESSION['search_criteria']['etd_end']." 23:59:59'";
			}else{
				$where_sql.= " AND f.expected_date > '".$_SESSION['search_criteria']['etd_start']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['etd_end'])){
			$where_sql.= " AND f.expected_date < '".$_SESSION['search_criteria']['etd_end']." 23:59:59'";
		}			
		
		$list_field = ' f.pvid, f.mark_date, f.printed_by, f.cid, f.reference, f.istatus, f.expected_date ';
		$start_row = 0;
		//默认值100000，相当于一页显示无限多条的记录了
		$end_row = 100000;
		
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$rs = $mysql->q($sql, $_SESSION['search_criteria']['start_date']." 00:00:00", $_SESSION['search_criteria']['end_date']." 23:59:59", (strlen(@$_SESSION['search_criteria']['user']))?'%'.$_SESSION['search_criteria']['user'].'%':'%'.$_SESSION['search_criteria']['status'].'%', '%'.$_SESSION['search_criteria']['status'].'%');
		if($info){
			//$rtn = $mysql->fetch();
			$rtn = $mysql->fetch(0, 1);
	?>
	<fieldset>
	<legend class='legend'>Information ( total : <?=$info?> )</legend>
		<table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">	
			<tr bgcolor='#EEEEEE'>
				<td>&nbsp;</td>
				<td colspan="9" align="center">SALES</td>
                <td colspan="3"></td>
				<td colspan="6" align="center">COST</td>
				<td colspan="2"></td>
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
				<th>PAYMENT RECEIVED(USD)</th>
                <th>ETD</th>
				<th>SHIP DATE</th>
                <th>PAYMENT DATE</th>
				<th>FTY ORDER #</th>
				<th>FTY ORD AMT(RMB)</th>
				<th>OH(RMB)</th>
				<th>FREIGHT COST(HKD)</th>
				<th>BANK CHARGE(USD)</th>
				<th>TOTAL COST(HKD)</th>
                <th>EST.GP(HKD)</th>
				<th>GP(HKD)</th>
			</tr>
	<?
			$total_pi = 0;
			$total_oa = 0;
			$total_pr = 0;
			$total_est_gp = 0;
			$total_gp = 0;
			foreach($rtn as $v){
				echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
				
				$customer = $v['cid'];
				
				//sales
				//proforma 的总和
				$proforma_total = 0;
				//invoice 的总和
				$sales_total = 0;
				//实际收到的钱
				$payment_total = 0;
				$invoice_all = '';
				
				//cost
				$purchase_all = '';
				$purchase_cost_total = 0;
				$overheads_cost_total = 0;
				$shipment_cost_total = 0;
				$bank_charge_cost_total = 0;
				
				//proforma
				$rs_proforma = $mysql->q('select pvid from proforma where istatus <> ? and pvid like ?', 'delete', '%'.$v['pvid'].'%');
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
						$invoice_all .= "<a href='?act=com-modifyinvoice&modid=".$w['vid']."'>".$w['vid']."</a><br />";
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
				
				//purchase
				$rs_purchase = $mysql->q('select pcid from purchase where istatus <> ? and reference = ?', 'delete', $v['pvid']);
				if($rs_purchase){
					$rtn_purchase = $mysql->fetch();
					foreach($rtn_purchase as $y){
						$purchase_all .= "<a href='?act=com-modifypurchase&modid=".$y['pcid']."'>".$y['pcid']."</a><br />";
						$rs_purchase_item = $mysql->q('select price, quantity from purchase_item where pcid = ?', $y['pcid']);
						if($rs_purchase_item){
							$rtn_purchase_item = $mysql->fetch();
							foreach($rtn_purchase_item as $j){
								$purchase_cost_total += $j['price'] * $j['quantity'];
							}
						}
						
						//overheads
						$rs_overheads = $mysql->q('select cost from overheads where po_no = ?', $y['pcid']);
						if($rs_overheads){
							$rtn_overheads_each = $mysql->fetch();
							foreach($rtn_overheads_each as $u){
								$overheads_cost_total += $u['cost'];
							}
						}
					}
				}
				
				//shipment
				$rs_shipment = $mysql->q('select cost from shipment where pi_no = ?', $v['pvid']);
				if($rs_shipment){
					$rtn_shipment = $mysql->fetch();
					foreach($rtn_shipment as $k){
						$shipment_cost_total += $k['cost'];
					}
				}
				
				//payment & other cost
				$rs_payment = $mysql->q('select amount, bank_charge from payment where pi_no = ?', $v['pvid']);
				if($rs_payment){
					$rtn_payment = $mysql->fetch();
					foreach($rtn_payment as $t){
						$payment_total += $t['amount'];
						$bank_charge_cost_total += $t['bank_charge'];
					}
				}	
				$total_pr += $payment_total;
				
				$rtn_shipment_date = $mysql->qone('select s_date from shipment where pi_no like ? and s_status = ? order by s_date desc', '%'.$v['pvid'].'%', 'Complete');
				$rtn_payment_delivery = $mysql->qone('select p_date from payment where pi_no like ? and p_status = ? order by p_date desc', '%'.$v['pvid'].'%', 'Balance');
				
				$rate_hkd = $mysql->qone('select rate from currency where type = ?', 'HKD');
				$rate_usd = $mysql->qone('select rate from currency where type = ?', 'USD');
				
				$payment_total_hkd = $payment_total / $rate_usd['rate'] * $rate_hkd['rate'];

				$cost_total = $shipment_cost_total + $bank_charge_cost_total / $rate_usd['rate'] * $rate_hkd['rate'];
				
				//毛利 pi total - 其他花费
				$est_gp = ($proforma_total / $rate_usd['rate'] * $rate_hkd['rate']) - $purchase_cost_total*$rate_hkd['rate'] - $overheads_cost_total*$rate_hkd['rate'] - $cost_total;
				$total_est_gp += $est_gp;
				
				$gp = $payment_total_hkd - $purchase_cost_total*$rate_hkd['rate'] - $overheads_cost_total*$rate_hkd['rate'] - $cost_total;
				$total_gp += $gp;
				
				echo "	<td>".$v['istatus']."</td>";
				echo "	<td>".(($v['mark_date'] == '') ? '' : date('Y-m-d', strtotime($v['mark_date'])))."</td>";
				echo "	<td>".$v['printed_by']."</td>";
				echo "	<td>".$customer."</td>";
				echo "	<td>".$v['reference']."</td>";
				echo "	<td><a href='?act=com-modifyproforma&modid=".$v['pvid']."'>".$v['pvid']."</a></td>";
				echo "  <td>".formatMoney($proforma_total)."</td>";
				echo "	<td align='left'>".$invoice_all."</td>";
				echo "	<td>".formatMoney($sales_total)."</td>";
				echo "	<td>".formatMoney($payment_total)."</td>";
				echo "  <td>".(($v['expected_date'] == '') ? '' : date('Y-m-d', strtotime($v['expected_date'])))."</td>";
				echo "	<td>".(($rtn_shipment_date['s_date'] == '') ? '' : date('Y-m-d', strtotime($rtn_shipment_date['s_date'])))."</td>";
				echo "	<td>".(($rtn_payment_delivery['p_date'] == '') ? '' : date('Y-m-d', strtotime($rtn_payment_delivery['p_date'])))."</td>";	
				echo "	<td align='left'>".$purchase_all."</td>";
				echo "	<td>".formatMoney($purchase_cost_total)."</td>";
				echo "	<td>".formatMoney($overheads_cost_total)."</td>";
				echo "	<td>".formatMoney($shipment_cost_total)."</td>";
				echo "	<td>".formatMoney($bank_charge_cost_total)."</td>";
				echo "	<td>".formatMoney($cost_total)."</td>";
				echo "	<td>".formatMoney($est_gp)."</td>";
				echo "	<td>".formatMoney($gp)."</td>";
				
				echo "</tr>";
			}
			echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'><td colspan='6' align='right'>Total：</td><td>".formatMoney($total_pi)."</td><td align='right'>Total：</td><td>".formatMoney($total_oa)."</td><td>".formatMoney($total_pr)."</td><td colspan='9' align='right'>Total：</td><td>".formatMoney($total_est_gp)."</td><td>".formatMoney($total_gp)."</td></tr>";
		}
	}
	else{
		echo "<script>alert('Please fill in any Date Start and Date End.')</script>";	
	}
}
?>	
                </table>
            </fieldset>
            </td>
            </tr>
        </table>
<?	
}
?>
