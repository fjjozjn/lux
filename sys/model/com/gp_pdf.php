<?php

/**
 * php create pdf
 * @package 
 * @abstract 
 * @author zjn
 * @since 2011.11
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');
 
if(!isset($_SESSION['logininfo'])){
	die();	
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');
require_once('../../../tcpdf/config/lang/eng.php');
require_once('../../../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF {
	//Page header
	public function Header() {
		// Logo
		$image_file = K_PATH_IMAGES.'header.jpg';
		$this->Image($image_file, 60, '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}	
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('arial', 'I', 10);
		// Page number
		$this->Cell(290, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}	
}

// create new PDF document
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ZJN');
$pdf->SetTitle('LUX');
$pdf->SetSubject('TCPDF');
$pdf->SetKeywords('TCPDF');

// set my header img
$pdf->SetHeaderData('header.jpg', 180, '', '');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('stsongstdlight', '', 9);//这个字体的英文显示太2了，挤到一坨
//$pdf->SetFont('arialunicid0', '', 10);//這個有的中文顯示不出來。。。
//$pdf->SetFont('courier', '', 10);//纯英文了，用这个吧
//$pdf->SetFont('times', '', 10);

// add a page
$pdf->AddPage();//怎麼能自動分頁？這樣我就不用AddPage了，每頁頂上的表頭問題也能解決

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

//獲取總頁數，也是通過product個數來計算，暫時不知道怎麼通過tcpdf函數獲取
if(isset($result2)){
	$page_nums = (count($result2) <= 6)?1:(intval((count($result2)-6)/8)+2);
}
// create some HTML content
$pdf->SetFont('times', '', 20);
//div的高度不可調，用span方便多了！！！
//找到調高度的方法了
$pdf->Ln(1);
$html = '<span align="right"><b>GROSS PROFIT</b><span/>';
//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(1);
	
$pdf->SetFont('arial', '', 8);
$html = '';


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

$html = '<hr height="2"><br/><br/><table border="1" align="center">	
		<tr bgcolor="#EEEEEE">
			<td>&nbsp;</td>
			<td colspan="9" align="center">SALES</td>
			<td colspan="3"></td>
			<td colspan="6" align="center">COST</td>
			<td colspan="2"></td>
		</tr>
		<tr bgcolor="#EEEEEE"> 
			<th></th>
			<th>PO REC DATE</th>
			<th>STAFF</th>
			<th>C ID</th>
			<th>CUST PO#</th>
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
		</tr>';
		
		$total_pi = 0;
		$total_oa = 0;
		$total_pr = 0;
		$total_est_gp = 0;
		$total_gp = 0;
		foreach($rtn as $v){
			
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
					$invoice_all .= $w['vid']."<br />";
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
					$purchase_all .= $y['pcid']."<br />";
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
			
			$html .="<tr>".
				"<td>".$v['istatus']."</td>".
				"<td>".($v['mark_date']?date('Y-m-d', strtotime($v['mark_date'])):$v['mark_date'])."</td>".
				"<td>".$v['printed_by']."</td>".
				"<td>".$customer."</td>".
				"<td>".$v['reference']."</td>".
				"<td>".$v['pvid'].'<br />'."</td>".
				"<td>".formatMoney($proforma_total)."</td>".
				"<td align='left'>".$invoice_all."</td>".
				"<td>".formatMoney($sales_total)."</td>".
				"<td>".formatMoney($payment_total)."</td>".
				"<td>".($v['expected_date']?date('Y-m-d', strtotime($v['expected_date'])):$v['expected_date'])."</td>".
				"<td>".($rtn_shipment_date['s_date']?date('Y-m-d', strtotime($rtn_shipment_date['s_date'])):$rtn_shipment_date['s_date'])."</td>".
				"<td>".($rtn_payment_delivery['p_date']?date('Y-m-d', strtotime($rtn_payment_delivery['p_date'])):$rtn_payment_delivery['p_date'])."</td>".
				"<td align='left'>".$purchase_all."</td>".
				"<td>".formatMoney($purchase_cost_total)."</td>".
				"<td>".formatMoney($overheads_cost_total)."</td>".
				"<td>".formatMoney($shipment_cost_total)."</td>".
				"<td>".formatMoney($bank_charge_cost_total)."</td>".
				"<td>".formatMoney($cost_total)."</td>".
				"<td>".formatMoney($est_gp)."</td>".
				"<td>".formatMoney($gp)."</td>".
				"</tr>";
		}
		$html .= '<tr>'.
			'<td colspan="6" align="right">Total:</td>'.
			'<td>'.formatMoney($total_pi).'</td>'.
			'<td align="right">Total:</td>'.
			'<td>'.formatMoney($total_oa).'</td>'.
			'<td>'.formatMoney($total_pr).'</td>'.
			'<td colspan="9" align="right">Total:</td>'.
			'<td>'.formatMoney($total_est_gp).'</td>'.
			'<td>'.formatMoney($total_gp).'</td>'.
			'</tr>';
	}


	$html .= "</table>";
	//echo $html;
    // output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');
	
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output('gp.pdf', 'I');
}else{
	die('Error!');	
}
?>
