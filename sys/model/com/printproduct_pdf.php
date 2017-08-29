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

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ZJN');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(4, 8, 4);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);
// ---------------------------------------------------------

// set font
$pdf->SetFont('arial', '', 6.5);


if (strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])){
				
	$info = $mysql->q('select pid from product where in_date between ? AND ? order by in_date desc', $_SESSION['search_criteria']['start_date'].' 00:00:00', $_SESSION['search_criteria']['end_date'].' 23:59:59');

	if($info){
		//$rtn = $mysql->fetch();
		$rtn = $mysql->fetch(0, 1);
		$print_nums = 0;
		//10列*27行
		while(count($rtn) > $print_nums){
			// add a page
			$pdf->AddPage();
			
			$html = '';	
			$html .= '<table width="100%" cellspacing="2" cellpadding="2.15" align="center">';	
			for($row = 0; $row < 27; $row++){
				$html .= '<tr>';
				for($col = 0; $col < 10; $col++){
					$html .= '<td width="10%" align="center" style="height:30">'.(isset($rtn[$row*10+$col+$print_nums]['pid'])?$rtn[$row*10+$col+$print_nums]['pid']:'&nbsp;').'</td>';
				}
				$html .= '</tr>';		
			}
			$html .= '</table>';
			$print_nums += 270;
			
			// output the HTML content
			$pdf->writeHTML($html, false, false, true, false, '');
		}
		
		// reset pointer to the last page
		$pdf->lastPage();
		
		// ---------------------------------------------------------
		
		//Close and output PDF document
		$pdf->Output('product.pdf', 'I');
	}else{
		die('Error.');	
	}
}else{
	die("请填写日期范围");
}	