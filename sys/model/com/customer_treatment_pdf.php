<?php

/**
 * php create pdf
 * @package 
 * @abstract 
 * @author zjn
 * @since 2012.08
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
		$this->Cell(200, 15, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}	
}

if(isset($_GET['id']) && $_GET['id'] != ''){
	//判断是否有访问权限
	/*
	if($_SESSION['logininfo']['aName'] != 'KEVIN' && $_SESSION['logininfo']['aName'] != 'zjn'){
		$rtn = $mysql->qone("select printed_by from customs_invoice where vid = ?", $_GET['vid']);
		if($rtn['printed_by'] != $_SESSION['logininfo']['aName']){
			$rtn1 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $rtn['printed_by']);
			$rtn2 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
			if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
				die('Without Permission To Access');
			}
		}
	}
	*/
	
	$result1 = $mysql->qone('select * from customer_treatment where id = ?', $_GET['id']);
	if(!$result1){
		die('Error(1)');	
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
	$html = '<span align="right"><b>Customer Treatment</b><span/>';
	//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	$pdf->SetFont('arial', '', 10);
	$html = '';
	$html .= '<hr height="2"><br />
        <table width="100%" align="left" cellpadding="2" cellspacing="2" border="0.5">
            <tr>    
                <td width="40%">Creation Date : </td>
                <td width="60%">'.$result1['creation_date'].'</td>
            </tr>        
            <tr>
                <td>Customer Code : </td>
                <td>'.$result1['customer_code'].'</td>
            </tr>
            <tr>    
                <td>Customer Name : </td>
                <td>'.$result1['customer_name'].'</td>
            </tr>
            <tr>
                <td>Mailing Address : </td>
                <td>'.$result1['mailing_address'].'</td>
            </tr>
            <tr>    
                <td>Email Address : </td>
                <td>'.$result1['email_address'].'</td>
            </tr>
            <tr>
                <td>Tel. No. & Fax No. : </td>
                <td>'.$result1['tel_and_fax'].'</td>
            </tr>
            <tr>    
                <td>Contact Person/Position</td>
                <td>'.$result1['contact_person_or_position'].'</td>
            </tr>  
            <tr>
                <td>Business Nature : </td>
                <td>'.$result1['business_nature'].'</td>
            </tr>
            <tr>    
                <td>Year of Establishment : </td>
                <td>'.$result1['year_of_establishment'].'</td>
            </tr>  
            <tr>
                <td>Customer s Client Base : </td>
                <td>'.$result1['customer_client_base'].'</td>
            </tr>
            <tr>    
                <td>Interested Product Range : </td>
                <td>'.$result1['interested_product_range'].'</td>
            </tr>
            <tr>
                <td>Special Requirement for product : </td>
                <td>'.$result1['special_requirement_for_product'].'</td>
            </tr>
            <tr>    
                <td>Target Price Range : </td>
                <td>'.$result1['target_price_range'].'</td>
            </tr>
            <tr>
                <td>Order quantity per style : </td>
                <td>'.$result1['order_quantity_per_style'].'</td>
            </tr>
            <tr>    
                <td>Annual Sales Turnover : </td>
                <td>'.$result1['annual_sales_turnover'].'</td>
            </tr>
            <tr>
                <td>Annual Cost Turnover : </td>
                <td>'.$result1['annual_cost_turnover'].'</td>
            </tr>
            <tr>    
                <td>Buying Cost : </td>
                <td>'.$result1['buying_cost'].'</td>
            </tr>
            <tr>
                <td>Nos.of Vendor Customer has : </td>
                <td>'.$result1['nos_of_vendor_customer_has'].'</td>
            </tr>
            <tr>    
                <td>Customer buying season : </td>
                <td>'.$result1['customer_buying_season'].'</td>
            </tr>
            <tr>
                <td>Customer celebration season : </td>
                <td>'.$result1['customer_celebration_season'].'</td>
            </tr>
            <tr>    
                <td>Trade Term : </td>
                <td>'.$result1['trade_term'].'</td>
            </tr>
            <tr>
                <td>Payment Term : </td>
                <td>'.$result1['payment_term'].'</td>
            </tr>
            <tr>    
                <td>Banker & Address : </td>
                <td>'.$result1['banker_and_address'].'</td>
            </tr>
            <tr>
                <td>Mark Up Formula : </td>
                <td>'.$result1['mark_up_formula'].'</td>
            </tr>
            <tr>    
                <td>Shipping Company : </td>
                <td>'.$result1['shipping_company'].'</td>
            </tr>
            <tr>
                <td>Shipping Marks : </td>
                <td>'.$result1['shipping_marks'].'</td>
            </tr>
            <tr>    
                <td>Port of Loading : </td>
                <td>'.$result1['port_of_loading'].'</td>
            </tr>
            <tr>
                <td>Port of Discharge : </td>
                <td>'.$result1['port_of_discharge'].'</td>
            </tr>
            <tr>    
                <td>Special Shipping Documents : </td>
                <td>'.$result1['special_shipping_documents'].'</td>
            </tr>                                                                                                             			
        </table>';
				
	// output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');
	
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output($result1['customer_code'].'.pdf', 'I');
	
	//============================================================+
	// END OF FILE                                                
	//============================================================+
	

}else{
	die('Error(2)');	
}
?>
