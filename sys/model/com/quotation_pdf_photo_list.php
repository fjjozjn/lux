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

if(isset($_GET['qid']) && $_GET['qid'] != ''){
	$result1 = $mysql->qone('select qid, send_to, attention, mark_date from quotation where qid = ?', $_GET['qid']);
	if(!$result1){
		die('Error(1)');	
	}
	$make_date = date('Y/m/d', strtotime($result1['mark_date']));
	$rs2 = $mysql->q('select pid, photos from quote_item where qid = ?', $_GET['qid']);
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
	if(isset($result2)){
		$page_nums = (count($result2) <= 6)?1:(intval((count($result2)-6)/8)+2);
	}
	// create some HTML content
	$pdf->SetFont('times', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);
	$html = '<span align="right"><b>Photo List</b><span/>';
	//$pdf->Line(15,87,195,87);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	//畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了	
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	//$pdf->SetFont('arial', '', 10);
	$pdf->SetFont('droidsansfallback', '', 10);
	$html = '';
	$html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%">TO: &nbsp;</td>
					<td width="35%"><b>'.$result1['send_to'].'</b></td>
					<td width="20%">QUOTATION NO.: &nbsp;</td>
					<td width="30%"><b>'.$result1['qid'].'</b></td>
				</tr>
				<tr>
					<td width="15%">ATTENTION: &nbsp;</td>
					<td width="35%">'.$result1['attention'].'</td>
					<td width="20%">DATE: &nbsp;</td>
					<td width="30%"><b>'.$make_date.'</b></td>
				</tr>																	
				</table><br />';
	$pdf->writeHTML($html, false, false, true, false, '');
	
	
$html = '';
$html .= '<table align="center" cellpadding="1" cellspacing="1">';
//$pdf->SetFont('arial', '', 10);
$pdf->SetFont('droidsansfallback', '', 10);

//暂定为一行显示4个photo，迟一点这个参数可用户自行设定
$col_num = 3;
//微调参数，调整width
$sign = 0;
//product 的個數
$rtn_num = count($result2);

for($i = 0; $i < count($result2); $i++){
	$img_html = '';
	if (is_file('../../' . $pic_path_com . $result2[$i]['photos']) == true) { 
		/*
		$arr = getimagesize('../../' . $pic_path_com . $result2[$i]['photos']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(115, 85, $pic_width, $pic_height);
		$img_html = '<img src="/sys/'.$pic_path_com . $result2[$i]['photos'].'" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/>';
		*/
		
		//壓縮圖片
		//$result2[$i]['photos']是原來的， $large_photo 是縮小後的
		//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
		$large_photo = 'l_' . $result2[$i]['photos'];
		//縮小的圖片不存在才進行縮小操作
		if (!is_file('../../' . $pic_path_small . $large_photo) == true) { 	
			makethumb('../../' . $pic_path_com . $result2[$i]['photos'], '../../' . $pic_path_small . $large_photo, 'l');
		}
		//寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用l_的圖片，分辨率高點
		$img_html = '<img src="/sys/'.$pic_path_small . 'l_'.$result2[$i]['photos'].'" align="middle" />';
	}

	if($i % $col_num == 0){
		$html .= '<tr align="center">';	
	}
	$sign++;
	//$img_html 为空的话也就显示个pid了，总不能什么都不显示吧
	$html .= '<td width="33%">'.$img_html.'<br />'.$result2[$i]['pid'].'</td>';
	if($sign == $col_num){
		$html .= '</tr><br />';
		$sign = 0;	
	}
}
if($sign != 0){
	$html .= '</tr>';	
}
$html .= '</table>';
$pdf->writeHTML($html, false, false, true, false, '');	
	
		
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output($result1['qid'].'.pdf', 'I');
	
	//============================================================+
	// END OF FILE                                                
	//============================================================+
	

}else{
	die('Error(3)');	
}
?>
