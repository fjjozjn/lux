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
$html = '<span align="right"><b>OVERDUE SHIPMENT</b><span/>';
//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(1);
	
$pdf->SetFont('arial', '', 8);
$html = '';


//20130406 因kevin说出不了PDF 提示 Error! 所以把这里的限制去掉了
if (/*(strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])) || (strlen(@$_SESSION['search_criteria']['shipment_start_date']) && strlen(@$_SESSION['search_criteria']['shipment_end_date'])) || (strlen(@$_SESSION['search_criteria']['etd_start']) && strlen(@$_SESSION['search_criteria']['etd_end']))*/ 1 ){	
	
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

$html = '<hr height="2"><br/><br/><table border="1" align="center">	
		<tr bgcolor="#EEEEEE">
			<td>&nbsp;</td>
			<td colspan="8" align="center">SALES</td>
			<td colspan="3"></td>
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
			<th>SHIP DATE</th>
			<th>ETD</th>
			<th>#of days</th>
		</tr>';
		
		$total_pi = 0;
		$total_oa = 0;
		$now = time();
		//20121010 pdf也按 of day 排序
		$html_array = array();
		$of_day_array = array();
		$i = 0;
		
		foreach($rtn as $v){
			
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
			
			//将 tr 整个字符串保存到数组中，方便排序			
			$html_array[$i] ="<tr>".
				"<td>".$v['istatus']."</td>".
				"<td>".($v['mark_date']?date('Y-m-d', strtotime($v['mark_date'])):$v['mark_date'])."</td>".
				"<td>".$v['printed_by']."</td>".
				"<td>".$customer."</td>".
				"<td>".$v['reference']."</td>".
				"<td>".$v['pvid'].'<br />'."</td>".
				"<td>".formatMoney($proforma_total)."</td>".
				"<td align='left'>".$invoice_all."</td>".
				"<td>".formatMoney($sales_total)."</td>".
				"<td>".($rtn_shipment_date['s_date']?date('Y-m-d', strtotime($rtn_shipment_date['s_date'])):$rtn_shipment_date['s_date'])."</td>".
				"<td>".(($v['expected_date'] == '') ? '' : date('Y-m-d', strtotime($v['expected_date'])))."</td>".
				"<td>".round($of_day)."</td>".
				"</tr>";
			//将 of_day 保存，按此排序	
			$of_day_array[$i] = $of_day;
			$i++;
		}
		
		//从大到小排序，key的位置不变
		arsort($of_day_array);
		foreach($of_day_array as $key => $v){
			$html .= $html_array[$key];
		}
				
		$html .= '<tr>'.
			'<td colspan="6" align="right">Total:</td>'.
			'<td>'.formatMoney($total_pi).'</td>'.
			'<td align="right">Total:</td>'.
			'<td>'.formatMoney($total_oa).'</td>'.
			'<td></td>'.
			'<td></td>'.
			'<td></td>'.
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
	$pdf->Output('overdue_shipment.pdf', 'I');
}else{
	die('Error!');	
}
?>
