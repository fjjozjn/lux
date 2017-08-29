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

if(isset($_GET['py_no']) && $_GET['py_no'] != ''){
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

    $result1 = $mysql->qone('select * from payment_new where py_no = ?', $_GET['py_no']);
    if(!$result1){
        die('Error(1)');
    }
    $result1['in_date'] = date('Y/m/d', strtotime($result1['in_date']));
    //20130828 改为没找到item，不die了
    $rs2 = $mysql->q('select * from payment_item_new where py_no = ?', $_GET['py_no']);
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
    $html = '<span align="right"><b>Payment Advice</b><span/>';
    //$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
    $pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Ln(1);

    $pdf->SetFont('arial', '', 10);
    $html = '';

    //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="35%">PAYMENT ADVICE NO. : &nbsp;</td>
					<td width="20%">'.$result1['py_no'].'</td>
					<td width="23%">BANK REF : &nbsp;</td>
					<td width="22%">'.$result1['bank_ref'].'</td>
				</tr>
				<tr>
					<td width="35%">BANK ACC : &nbsp;</td>
					<td width="20%">'.$result1['bank_acc'].'</td>
					<td width="23%">VALUE DATE : &nbsp;</td>
					<td width="22%">'.date('Y-m-d', strtotime($result1['value_date'])).'</td>
				</tr>	
				<tr>
					<td width="35%">REMITTING AMOUNT : &nbsp;</td>
					<td width="20%">'.number_format($result1['remitting_amount'], 2, '.', '').'</td>
					<td width="23%">CURRENCY : &nbsp;</td>
					<td width="22%">'.$result1['currency'].'</td>
				</tr>
				<tr>
					<td width="35%">TOTAL BANK CHARGES (HKD) : &nbsp;</td>
					<td width="20%">'.number_format($result1['total_bank_charges'], 2, '.', '').'</td>
					<td width="23%">REMITTER : &nbsp;</td>
					<td width="22%">'.$result1['remitter'].'</td>
				</tr>
				<tr>
					<td width="35%">MESSAGE REMARK : &nbsp;</td>
					<td width="20%">'.$result1['message_remark'].'</td>
					<td width="23%">&nbsp;</td>
					<td width="22%">&nbsp;</td>
				</tr>
				</table><div></div>';
    $pdf->writeHTML($html, false, false, true, false, '');

    //20130828 要result2有东西，才显示下面，不然下面<table>标签不全，出PDF会报错
    if($rs2){
        $html = '';
        //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
        $html .= '<table align="right" cellpadding="1" cellspacing="1">
                    <tr>
                        <th width="10%">PI/CN</th>
                        <th width="20%">PI/CN #</th>
                        <th width="18%">TOTAL(USD)</th>
                        <th width="18%">OUTSTANDING</th>
                        <th width="17%">RECEIVED</th>
                        <th width="17%">BALANCE</th>
                    </tr></table>';
        $pdf->writeHTML($html, false, false, true, false, '');
        $pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

        $all_received = 0;
        $html = '';
        $html .= '<table align="right" cellpadding="1" cellspacing="1">';
        for($i = 0; $i < count($result2); $i++){
            $html .= '<tr>
                        <td width="10%">'.$result2[$i]['pi_or_cn'].'</td>
                        <td width="20%">'.$result2[$i]['pi_or_cn_no'].'</td>
                        <td width="18%">'.number_format($result2[$i]['total'], 2, '.', '').'</td>
                        <td width="18%">'.number_format($result2[$i]['outstanding'], 2, '.', '').'</td>
                        <td width="17%">'.(($result2[$i]['pi_or_cn'] == 'CN')?'-':'').number_format($result2[$i]['received'], 2, '.', '').'</td>
                        <td width="17%">'.number_format($result2[$i]['balance'], 2, '.', '').'</td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>';

            //20140520 可以填负号，所以都用加
            $all_received += $result2[$i]['received'];
/*            if($result2[$i]['pi_or_cn'] == 'PI'){
                $all_received += $result2[$i]['received'];
            }else if($result2[$i]['pi_or_cn'] == 'CN' || $result2[$i]['pi_or_cn'] == 'CUSTOMER BANK CHARGE'){
                $all_received -= $result2[$i]['received'];
            }else{
                $all_received += $result2[$i]['received'];
            }*/
        }

        $html .= '<hr />';

        $html .= '<tr>
                        <td width="10%"></td>
                        <td width="20%"></td>
                        <td width="18%"></td>
                        <td width="18%">TOTAL : </td>
                        <td width="17%">'.number_format($all_received, 2, '.', '').'</td>
                        <td width="17%"></td>
                    </tr>';
        $html .= '<tr>
                        <td width="10%"></td>
                        <td width="20%"></td>
                        <td width="18%"></td>
                        <td width="18%">DISCREPANCY : </td>
                        <td width="17%">'.number_format(($result1['remitting_amount']-$all_received), 2, '.', '').'</td>
                        <td width="17%"></td>
                    </tr>';
        $html .= '</table>';
        $pdf->writeHTML($html, false, false, true, false, '');
        //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

        //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
        //$html .= ($i == count($result2)-1)?'':'<hr />';
        //$pdf->Ln(2);
    }
    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['py_no'].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


}else{
    die('Error(3)');
}