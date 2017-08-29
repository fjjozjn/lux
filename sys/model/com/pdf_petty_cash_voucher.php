<?php


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
        /*$image_file = K_PATH_IMAGES.'header.jpg';
        $this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);*/
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

if(isset($_GET['pcv_id']) && $_GET['pcv_id'] != ''){
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

    $result1 = $mysql->qone('select * from sys_petty_cash_voucher where id = ?', $_GET['pcv_id']);
    if(!$result1){
        die('Error(1)');
    }
    $result1['in_date'] = date('Y/m/d', strtotime($result1['in_date']));

    //20130828 改为没找到item，不die了
    $rs2 = $mysql->q('select * from sys_petty_cash_voucher_item where main_id = ?', $_GET['pcv_id']);
    if($rs2){
        $result2 = $mysql->fetch();
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
    //$pdf->SetHeaderData('header.jpg', 180, '', '');

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
    //$pdf->SetFont('times', '', 20);
    //div的高度不可調，用span方便多了！！！
    //找到調高度的方法了
    //$pdf->Ln(1);
    //$html = '<span align="right"><b>Payment Advice</b><span/>';
    //$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
    //$pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Ln(1);

    $pdf->SetFont('droidsansfallback', '', 10);
    $html = '';

    //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
    $html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<td width="25%"></td>
					<td width="35%">Lux Design Limited</td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%">樂思(配飾) 設計有限公司</td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%">PETTY CASH  VOUCHER</td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%"></td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%"></td>
					<td width="12%"></td>
					<td width="12%">NO.</td>
					<td width="16%">'.$result1['pcv_id'].'</td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%"></td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%"></td>
					<td width="12%"></td>
					<td width="12%">Date.</td>
					<td width="16%">'.$result1['in_date'].'</td>
				</tr>
				<tr>
					<td width="25%"></td>
					<td width="35%"></td>
					<td width="12%"></td>
					<td width="12%"></td>
					<td width="16%"></td>
				</tr>
				<tr>
					<td width="25%">Account Name</td>
					<td width="35%">Description</td>
					<td width="12%">CNY</td>
					<td width="12%">Rate</td>
					<td width="16%">Amount</td>
				</tr></table>';

    $html .= '<table border="1" align="center" cellpadding="1" cellspacing="1">';
    //20130828 要result2有东西，才显示下面，不然下面<table>标签不全，出PDF会报错
    if($rs2){
        $cny_total = 0;
        $amount_total = 0;
        foreach($result2 as $v){
            $html .= '<tr>
                <td width="25%">'.$v['account_name'].'</td>
                <td width="35%">'.$v['description'].'</td>
                <td width="12%">'.$v['cny'].'</td>
                <td width="12%">'.$v['rate'].'</td>
                <td width="16%">'.$v['amount'].'</td>
            </tr>';
            $cny_total += $v['cny'];
            $amount_total += $v['amount'];
        }

    }

    $html .= '</table>';

    $html .= '<table align="center" cellpadding="1" cellspacing="1"><tr>
                <td width="25%"></td>
                <td width="35%" align="right">Total:</td>
                <td width="12%">'.'￥ '.my_formatMoney($cny_total).'</td>
                <td width="12%"></td>
                <td width="16%">'.'HK$ '.my_formatMoney($amount_total).'</td>
            </tr></table>';

    $pdf->writeHTML($html, false, false, true, false, '');

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['pcv_id'].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


}else{
    die('Error(3)');
}