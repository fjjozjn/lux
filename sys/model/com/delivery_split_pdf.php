<?php

/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since 2011.9
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['logininfo'])){
	die();
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/tcpdf/config/lang/eng.php');
require_once($_SERVER['DOCUMENT_ROOT'].'./tcpdf/tcpdf.php');




// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF {
	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'header.jpg';
		//$this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		global $mysql;
		//fb($_SESSION['ftylogininfo']);
		$rtn_delivery = $mysql->qone('select sid from delivery where d_id = ?', $_GET['d_id']);
		$rtn = $mysql->qone('select sid, name, name_en from supplier where sid = ?', $rtn_delivery['sid']);
		$this->SetFont('droidsansfallback', '', 20);
		$this->MultiCell(55, 10, $rtn['name']?$rtn['name']:'Lux 内部使用', 0, 'L', 0, 0, 15, 5, true);
		$this->SetFont('droidsansfallback', '', 10);
		$this->MultiCell(55, 10, $rtn['name_en']?$rtn['name_en']:'', 0, 'L', 0, 0, 15, 15, true);
		$rtn_contact = $mysql->qone('select address, tel1 from contact where sid = ?', $rtn['sid']);
		if($rtn){
			$this->MultiCell(100, 20, '地址：'.trim($rtn_contact['address'])."\r\n".'电话：'.trim($rtn_contact['tel1']), 0, 'R', 0, 0, 95, 7, true);
		}
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('droidsansfallback', 'I', 10);
		// Page number
		$this->Cell(200, 10, '第 '.$this->getAliasNumPage().' 页，共 '.$this->getAliasNbPages().' 页', 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
}


if(isset($_GET['d_id']) && $_GET['d_id'] != ''){

	// create new PDF document
	// 第一个参数默认是PDF_PAGE_ORIENTATION 这个是字符'P'  改为'L'就是横向输出pdf文档了
	//20130104 又改为P了
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('ZJN');
	$pdf->SetTitle('LUX');
	$pdf->SetSubject('TCPDF');
	$pdf->SetKeywords('TCPDF');

	//mod 20120926 去掉顶上的公司标志
	// set my header img
	//$pdf->SetHeaderData('header.jpg', 180, '', '');

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



	$mod_result = $mysql->qone('select * from delivery where d_id = ?', $_GET['d_id']);

	$rs = $mysql->q('select po_id, sum(quantity) as quantity from delivery_item where d_id = ? group by po_id', $mod_result['d_id']);
	$rtn_po_item = $mysql->fetch();
	$total_quantity = 0;//总数，为计算运费分摊
	foreach($rtn_po_item as $v){
		$total_quantity += $v['quantity'];
	}
	foreach($rtn_po_item as $v){

		$mod_item_result = $mysql->q('select * from delivery_item where d_id = ? and po_id = ?', $mod_result['d_id'], $v['po_id']);
		if($mod_item_result){
			$d_item_rtn = $mysql->fetch();
			$d_item_num_mod = count($d_item_rtn);
		}
		$mod_result['d_date'] = date('Y/m/d', strtotime($mod_result['d_date']));

		// ---------------------------------------------------------

		// set font
		//$pdf->SetFont('stsongstdlight', '', 9);//这个字体的英文显示太2了，挤到一坨
		//$pdf->SetFont('arialunicid0', '', 10);//這個有的中文顯示不出來。。。
		//$pdf->SetFont('courier', '', 10);//纯英文了，用这个吧
		//$pdf->SetFont('times', '', 10);

		// add a page
		$pdf->AddPage();//怎麼能自動分頁？這樣我就不用AddPage了，每頁頂上的表頭問題也能解決

		// create some HTML content
		$pdf->SetFont('droidsansfallback', '', 15);
		//div的高度不可調，用span方便多了！！！
		//找到調高度的方法了
		$pdf->Ln(1);
		$html = '<span align="center"><b>出货发票</b><span/>';
		//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
		$pdf->writeHTML($html, true, false, true, false, '');
		//$pdf->Ln(1);

		// set font
		$pdf->SetFont('droidsansfallback', '', 9);

		// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
		// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

	// create some HTML content

	// table 里不能加这个 cellspacing="0" ，加了就歪了
	$html = '<br /><br /><table align="left" cellpadding="1" cellspacing="1">
			<tr class="formtitle">
				<td width="15%">客户公司 ：</td>
				<td width="35%">'.$mod_result['client_company'].'</td>
				<td width="15%"></td>
				<td width="35%"></td>
			</tr>
			<tr class="formtitle">
				<td width="15%">出货单号 ：</td>
				<td width="35%">'.$mod_result['d_id'].'</td>
				<td width="15%">地址 ：</td>
				<td width="35%">'.$mod_result['address'].'</td>
			</tr>
		</table>';

	$html .= '<div class="line"></div>
		<table width="100%" border="0.5"  align="left">
			<tr bgcolor="#EEEEEE">
				<th width="4%">箱数</th>
				<th width="4%">内箱</th>
				<th width="13%">订单号</th>
				<th width="8%">客户</th>
				<th width="8%">款号</th>
				<th width="8%">客号</th>
				<th width="7%">数量</th>
				<th width="8%">单价(元)</th>
				<th width="8%">金额(元)</th>
				<th width="11%">外箱重量(KG)</th>
				<th width="10%">尺寸(CM)</th>
				<th width="11%">备注</th>
			</tr>
			<tbody id="tbody" class="delivery" align="center">';

				$total_num = 0;
				$total_all = 0;
				$total_weight = 0;
				for($i = 0; $i < $d_item_num_mod; $i++){
					$html .= '<tr id="'.$d_item_rtn[$i]['po_id'].'">
					<td>'.$d_item_rtn[$i]['box_num'].'</td>
					<td>'.$d_item_rtn[$i]['inner_box_num'].'</td>
					<td>'.$d_item_rtn[$i]['po_id'].'</td>
					<td>'.$d_item_rtn[$i]['sid'].'</td>
					<td>'.$d_item_rtn[$i]['p_id'].'</td>
					<td>'.$d_item_rtn[$i]['c_code'].'</td>
					<td align="right">'.$d_item_rtn[$i]['quantity'].'</td>
					<td align="right">'.formatMoney($d_item_rtn[$i]['price']).'</td>
					<td id="sub" align="right">'.formatMoney($d_item_rtn[$i]['quantity']*$d_item_rtn[$i]['price']).'</td>
					<td align="right">'.$d_item_rtn[$i]['weight'].'</td>
					<td align="right">'.($d_item_rtn[$i]['size_l']!=''?$d_item_rtn[$i]['size_l']:0).'*'.($d_item_rtn[$i]['size_w']!=''?$d_item_rtn[$i]['size_w']:0).'*'.($d_item_rtn[$i]['size_h']!=''?$d_item_rtn[$i]['size_h']:0).'</td>
					<td>'.$d_item_rtn[$i]['remark'].'</td>
					</tr>';

					$total_num += $d_item_rtn[$i]['quantity'];
					$total_all += $d_item_rtn[$i]['quantity']*$d_item_rtn[$i]['price'];
					$total_weight += $d_item_rtn[$i]['weight'];
				}
				$express_cost = $mod_result['express_cost']*($total_num/$total_quantity);
				$total_all += $express_cost;
				$html .= '</tbody>';
				$html .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td align="right">运费：</td><td align="right">'.formatMoney($express_cost).'</td><td></td><td align="right">运单编号：</td><td align="center">'.$mod_result['express_id'].'</td></tr>';

				$html .= '<tr><td></td><td></td><td></td><td></td><td></td><td align="right">总数：</td><td id="totalQ" align="right">'.$total_num.'</td><td align="right">合计：</td><td id="total" align="right">'.formatMoney($total_all).'</td><td align="right">'.$total_weight.'</td><td align="right">出货日期：</td><td align="center">'.$mod_result['d_date'].'</td></tr>';

	$html .= '</table>';

	$html .= '<br /><table border="0"><tr><td width="75%">备注 ： '.$mod_result['remark'].'</td><td>审核 ： '.$mod_result['staff'].'</td></tr></table>';
	/*
		$html .= '<br /><br /><table width="100%" style= "border:1px solid black" cellpadding="3" cellspacing="3">
				<tr align="left">
					<td width="50%"><font size="+2">签署/盖章:</font></td>
				</tr>
				<br />
				<br />
				<br />
				<br />
				<tr valign="baseline">
					<td><hr width="60%" valign="baseline"></td>
				</tr>
				<tr>
					<td>日期:</td>
				</tr>
			</table>';
			*/
	$html .= '<br /><br /><table width="50%"><tr><td><img src="/fty/images/sign_delivery.jpg" /></td></tr></table>';

	// output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');

	// reset pointer to the last page
	$pdf->lastPage();


}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($mod_result['d_id'].'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+


}else{
	die('系統故障(2)');
}