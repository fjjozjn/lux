<?php

/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since 2016.05.03
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
        $this->SetFont('droidsansfallback', 'I', 10);
        // Page number
        $this->Cell(200, 10, '第 '.$this->getAliasNumPage().' 頁，共 '.$this->getAliasNbPages().' 頁', 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

if(isset($_GET['fcb_id']) && $_GET['fcb_id'] != ''){
    //判断是否有访问权限
//    if(!isSysAdmin()){
//        $rtn = $mysql->qone("select created_by from purchase where pcid = ?", $_GET['pcid']);
//        if($rtn['created_by'] != $_SESSION['logininfo']['aNameChi']){
//            $rtn1 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminNameChi = ?', $rtn['created_by']);
//            $rtn2 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
//            if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
//                die('Without Permission To Access');
//            }
//        }
//    }

    $result1 = $mysql->qone('select fcb.*, s.name, t.AdminNameChi from fty_chargeback_form fcb, supplier s, tw_admin t where fcb.fty_sid = s.sid
and fcb.staff = t.AdminName and fcb_id = ?', $_GET['fcb_id']);
    if(!$result1){
        die('Error(1)');
    }
    $form_date = date('Y/m/d', strtotime($result1['form_date']));
    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $reason = str_replace("\r\n", '<br />', $result1['reason']);

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

    // create some HTML content
    $pdf->SetFont('droidsansfallback', '', 20);
    //div的高度不可調，用span方便多了！！！
    //找到調高度的方法了
    $pdf->Ln(1);
    $html = '<span align="right"><b>扣款單</b><span/>';
    //畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了
    //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
    $pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Ln(1);

    $pdf->SetFont('droidsansfallback', '', 10);
    $html = '';
    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%">编号: &nbsp;</td>
					<td width="35%"><b>'.$result1['fcb_id'].'</b></td>
					<td width="20%">PO #: &nbsp;</td>
					<td width="30%"><b>'.$result1['pcid'].'</b></td>
				</tr>
				<tr>
					<td width="15%">工厂: &nbsp;</td>
					<td width="35%"><b>'.$result1['name'].'</b></td>
					<td width="20%">致: &nbsp;</td>
					<td width="30%"><b>'.$result1['send_to'].'</b></td>
				</tr>
				<tr>
					<td width="15%">日期: &nbsp;</td>
					<td width="35%"><b>'.$form_date.'</b></td>
					<td width="20%">負責人: &nbsp;</td>
					<td width="30%"><b>'.$result1['AdminNameChi'].'</b></td>
				</tr>
				<tr>
					<td width="15%">备注: &nbsp;</td>
					<td width="35%"><b>'.$result1['remark'].'</b></td>
					<td width="20%">&nbsp;</td>
					<td width="30%">&nbsp;</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
                <tr>
					<td width="15%">扣款内容原因: &nbsp;</td>
					<td colspan="3"><b>'.$reason.'</b></td>
				</tr>
				</table>
				<hr />
				<table>
				<tr>
					<td width="15%">扣款金额(RMB): &nbsp;</td>
					<td width="35%"><b>'.$result1['money'].'</b></td>
					<td width="20%">&nbsp;</td>
					<td width="30%">&nbsp;</td>
				</tr>
				</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(1);
    $html = '<table width="100%"><tr><td><img src="/sys/images/sign.jpg" /></td></tr></table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['fcb_id'].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


}else{
    die('Error(3)');
}
?>
