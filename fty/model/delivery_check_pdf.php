<?php

/**
 * php create pdf
 * @package 
 * @abstract 
 * @author zjn
 * @since 2011.9
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['ftylogininfo'])){
	die('Please login!');	
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');
require_once('../../tcpdf/config/lang/eng.php');
require_once('../../tcpdf/tcpdf.php');



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
		$this->SetFont('droidsansfallback', 'I', 10);
		// Page number
		$this->Cell(200, 10, '第 '.$this->getAliasNumPage().' 页，共 '.$this->getAliasNbPages().' 页', 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}	
}

if(isset($_GET['d_id']) && $_GET['d_id'] != ''){
	//判断是否有访问权限
	//isFtyAdmin 放在fty/in38/admin_function.php 里，这里用的是sys的函数集，所以再用fty的会有函数重名错误
	if($_SESSION['ftylogininfo']['aName'] != 'ZJN' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
		$rtn = $mysql->qone("select sid from delivery where d_id = ?", $_GET['d_id']);
		if($rtn['sid'] != $_SESSION['ftylogininfo']['aName']){
			die('Without Permission To Access');
		}
	}
	
	$rs = $mysql->q('select po_id, p_id from delivery_item where d_id = ?', $_GET['d_id']);
	if(!$rs){
		die('Error(1)');	
	}else{
		$result = $mysql->fetch();	
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
	if(isset($result2)){
		$page_nums = (count($result2) <= 6)?1:(intval((count($result2)-6)/8)+2);
	}
	// create some HTML content
	$pdf->SetFont('droidsansfallback', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);
	$html = '<span align="right"><b>产品送检单</b><span/>';
	//畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了
	//$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	$pdf->SetFont('droidsansfallback', '', 10);
	$html = '';
	$html .= '<hr height="2"><br /><br /><table><tr><td width="60%">'.date('Y年m月d日').'</td><td align="center" ma>编号 ： '.$_GET['d_id'].'</td></tr></table>';
	
	if(!empty($result)){
		foreach($result as $v){
			$html .= '<br /><table border="1">
						<tr>
							<td>产品编号 ： </td>
							<td>'.$v['p_id'].'</td>
							<td>生产单号 ： </td>
							<td>'.$v['po_id'].'</td>
							<td>备注</td>
						</tr>
						<tr>
							<td>送检数量 ： </td>
							<td></td>
							<td>检验结果 ： </td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>送检时间 : </td>
							<td></td>
							<td>抽检时间 : </td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>送检人 ： </td>
							<td></td>
							<td>检验员 ： </td>
							<td></td>
							<td></td>
						</tr>
					</table><br />';
		}
	}
	
	// output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');
	
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output($result1['pcid'].'.pdf', 'I');
	
	//============================================================+
	// END OF FILE                                                
	//============================================================+
	

}else{
	die('Error(2)');	
}
?>
