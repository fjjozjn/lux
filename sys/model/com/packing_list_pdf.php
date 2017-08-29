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
		$this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}	
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('arial', 'I', 10);
		// Page number
		$this->Cell(200, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}	
}

if(isset($_GET['pl_id']) && $_GET['pl_id'] != ''){
	//判断是否有访问权限
	/*
	if(!isSysAdmin()){
		$rtn = $mysql->qone("select printed_by from invoice where pl_id = ?", $_GET['pl_id']);
		if($rtn['printed_by'] != $_SESSION['logininfo']['aName']){
			$rtn1 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $rtn['printed_by']);
			$rtn2 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
			if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
				die('Without Permission To Access');
			}
		}
	}
	*/
	
	//修改printed_date
	$mysql->q('update packing_list set printed_date = ? where pl_id = ?', dateMore(), $_GET['pl_id']);
	
	$result1 = $mysql->qone('select * from packing_list where pl_id = ?', $_GET['pl_id']);
	if(!$result1){
		die('Error(1)');	
	}
	// \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
	$ship_to = str_replace("\r\n", '<br />', $result1['ship_to']);
	$in_date = date('Y/m/d', strtotime($result1['in_date']));
	$printed_date = date('Y/m/d', strtotime($result1['printed_date']));
    //20130731 加当同属于一个cart_no的时候后面几项有值的排前面
	$rs2 = $mysql->q('select * from packing_list_item where pl_id = ? order by cart_no asc, gross_weight desc, size_l desc, size_w desc, size_h desc, cbm desc', $_GET['pl_id']);
	if($rs2){
		$result2 = $mysql->fetch();	
	}else{
		die('Error(2)');	
	}

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
	//20130731（还是不行。。。）
    //20130822 用$pdf->getAliasNbPages()输出会出现不可识别字符，所以去掉了
	$page_nums = '';//$pdf->getAliasNbPages();

	// create some HTML content
	$pdf->SetFont('times', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);
	$html = '<span align="right"><b>Packing List</b><span/>';
	//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);

	$pdf->SetFont('arial', '', 10);
	$html = '';

    //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
	$html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%" rowspan="4">TO: &nbsp;</td>
					<td width="40%" rowspan="4">'.$ship_to.'</td>
					<td width="23%">PACKING LIST NO.: &nbsp;</td>
					<td width="22%">'.$result1['pl_id'].'</td>
				</tr>
				<tr>
					<td width="23%">DATE: &nbsp;</td>
					<td width="22%">'.$in_date.'</td>
				</tr>
				<tr>
					<td width="23%">REFERENCE NO.: &nbsp;</td>
					<td width="22%">&nbsp;</td>
				</tr>
				<tr>
					<td width="23%">UNIT: &nbsp;</td>
					<td width="22%">'.$result1['unit'].'</td>
				</tr>		
				<tr>
					<td width="15%">TEL: &nbsp;</td>
					<td width="40%">'.$result1['tel'].'</td>
					<td width="23%">PAGE: &nbsp;</td>
					<td width="22%">'.$page_nums.'</td>
				</tr>	
				<tr>
					<td width="15%">P.O.#: &nbsp;</td>
					<td width="40%">'.$result1['reference_no'].'</td>
					<td width="23%">PRINTED BY: &nbsp;</td>
					<td width="22%">'.$_SESSION["logininfo"]["aName"].'</td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="40%">&nbsp;</td>
					<td width="23%">PRINTED DATE: &nbsp;</td>
					<td width="22%">'.$printed_date.'</td>
				</tr>																															
				</table><div></div>';
	$pdf->writeHTML($html, false, false, true, false, '');

	$html = '';
	//cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
	$html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<th width="10%">CARTON#</th>
					<th width="16%">CLIENT#</th>
					<th width="16%">ITEM#</th>
					<th width="10%">QTY</th>
					<th width="16%">GROSS WEIGHT (KG)</th>
					<th width="18%">MEASURMENT (CM)</th>
					<th width="14%">CBM</th>
				</tr></table>';
	$pdf->SetFont('arial', '', 10);//20120704 ie6不显示//$pdf->SetFont('arialbd', '', 10);
	$pdf->writeHTML($html, false, false, true, false, '');
	$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

	$html = '';

	$pdf->SetFont('arial', '', 10);

	$html = '';
	$html .= '<table align="center" cellpadding="1" cellspacing="1">';
	for($i = 0; $i < count($result2); $i++){

        //20130731 下面三个值为0或空则不显示
        $gross_weight = ($result2[$i]['gross_weight'] == '' || $result2[$i]['gross_weight'] == 0)?'':$result2[$i]['gross_weight']. 'KG';
        $measurment = '';
        $cbm = '';
        if(($result2[$i]['size_l'] == '' || $result2[$i]['size_l'] == 0) &&
            ($result2[$i]['size_w'] == '' || $result2[$i]['size_w'] == 0) &&
            ($result2[$i]['size_h'] == '' || $result2[$i]['size_h'] == 0)){
            $measurment = '';
            $cbm = '';
        }else{
            $measurment = $result2[$i]['size_l'].' * '.$result2[$i]['size_w'].' * '.$result2[$i]['size_h'];
            $cbm = $result2[$i]['size_l']*$result2[$i]['size_w']*$result2[$i]['size_h']/1000000;
        }

		$html .= '<tr>
					<td width="10%">'.$result2[$i]['cart_no'].'</td>
					<td width="16%">'.$result2[$i]['client_no'].'</td>
					<td width="16%">'.$result2[$i]['item'].'</td>
					<td width="10%">'.$result2[$i]['qty'].'</td>
					<td width="16%">'.$gross_weight.'</td>
					<td width="18%">'.$measurment.'</td>
					<td width="14%">'.round($cbm,3).'</td>
				</tr>
				<tr><td>&nbsp;</td></tr>';	
	}
	$html .= '</table>';
	$pdf->writeHTML($html, false, false, true, false, '');
	$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());
	$html = '';
	$html .= '<table align="center" cellpadding="1" cellspacing="1">';
	$html .= '<tr>
				<td width="42%">TOTAL: '.$result1['total_cart'].' CARTONS</td>
				<td width="10%">'.$result1['total_qty'].' '.$result1['unit'].'</td>
				<td width="16%">'.$result1['total_weight'].' KG</td>
				<td width="18%">&nbsp;</td>
				<td width="14%">'.$result1['total_cbm'].'</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td colspan="7" align="left">COUNTRY OF ORIGIN: CHINA</td>
			</tr>
			<tr>
				<td colspan="7" align="left">REMARK : '.$result1['remark'].'</td>
			</tr>';;
	$html .= '</table>';		
	//還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
	//$html .= ($i == count($result2)-1)?'':'<hr />';		
	$pdf->writeHTML($html, false, false, true, false, '');
	$pdf->Ln(2);

	// reset pointer to the last page
	$pdf->lastPage();

	// ---------------------------------------------------------

	//Close and output PDF document
	$pdf->Output($result1['pl_id'].'.pdf', 'I');

	//============================================================+
	// END OF FILE                                                
	//============================================================+
		
	
}else{
	die('Error(3)');	
}