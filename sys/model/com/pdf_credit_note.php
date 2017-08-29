<?php

/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since 2011.11
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

//以防未登入就直接访问 
if (!isset($_SESSION['logininfo'])) {
    die('');
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');
require_once('../../../tcpdf/config/lang/eng.php');
require_once('../../../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        // Logo
        $image_file = K_PATH_IMAGES . 'header.jpg';
        $this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('arial', 'I', 10);
        // Page number
        $this->Cell(200, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

if (isset($_GET['cn_no']) && $_GET['cn_no'] != '') {
    //判断是否有访问权限
    if (!isSysAdmin()) {
        $rtn = $mysql->qone("select created_by from credit_note where cn_no = ?", $_GET['cn_no']);
        if ($rtn['created_by'] != $_SESSION['logininfo']['aName']) {
            if (!judgeUserPermGroup($rtn['created_by'])) {
                die('Without Permission To Access');
            }
        }
    }

    //修改printed_date
    $mysql->q('update credit_note set printed_by = ?, printed_date = ? where cn_no = ?', $_SESSION["logininfo"]["aName"], dateMore(), $_GET['cn_no']);

    $result1 = $mysql->qone('select * from credit_note where cn_no = ?', $_GET['cn_no']);
    if (!$result1) {
        die('Error(1)');
    }

    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $send_to = str_replace("\r\n", '<br />', $result1['send_to']);
    $in_date = date('Y/m/d', strtotime($result1['in_date']));
    $printed_date = date('Y/m/d', strtotime($result1['printed_date']));
    $rs2 = $mysql->q('select * from credit_note_item where cn_no = ?', $_GET['cn_no']);
    if ($rs2) {
        $result2 = $mysql->fetch();
    } else {
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
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //set auto page breaks
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

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

    //獲取總頁數，也是通過 item 個數來計算，暫時不知道怎麼通過tcpdf函數獲取
    $each_page_item_num = 10;
    if (isset($result2)) {
        $page_nums = (count($result2) <= $each_page_item_num) ? 1 : (intval((count($result2) - $each_page_item_num) / $each_page_item_num) + 2);
    }
    // create some HTML content
    $pdf->SetFont('times', '', 20);
    //div的高度不可調，用span方便多了！！！
    //找到調高度的方法了
    $pdf->Ln(1);
    $html = '<span align="right"><b>Credit Note</b><span/>';
    //$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
    $pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Ln(1);

    $pdf->SetFont('arial', '', 10);
    $html = '';
    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%" rowspan="4">TO: &nbsp;</td>
					<td width="43%" rowspan="4">' . $send_to . '</td>
					<td width="20%">CREDIT NOTE NO.: &nbsp;</td>
					<td width="22%"><b>' . $result1['cn_no'] . '</b></td>
				</tr>
				<tr>
					<td width="20%">DATE: &nbsp;</td>
					<td width="22%"><b>' . $in_date . '</b></td>
				</tr>
				<tr>
					<td width="20%">CURRENCY: &nbsp;</td>
					<td width="22%"><b>' . $result1['currency'] . '</b></td>
				</tr>
				<tr>
					<td width="20%">PAGE: &nbsp;</td>
					<td width="22%"><b>' . $page_nums . '</b></td>
				</tr>		
				<tr>
					<td width="15%">ATTENTION: &nbsp;</td>
					<td width="43%"><b>' . $result1['attention'] . '</b></td>
					<td width="20%">PRINTED BY: &nbsp;</td>
					<td width="22%"><b>' . $result1['printed_by'] . '</b></td>
				</tr>	
				<tr>
					<td width="15%">TEL: &nbsp;</td>
					<td width="43%"><b>' . $result1['tel'] . '</b></td>
					<td width="20%">PRINTED DATE: &nbsp;</td>
					<td width="22%"><b>' . $printed_date . '</b></td>
				</tr>
				<tr>
					<td width="15%">FAX: &nbsp;</td>
					<td width="43%"><b>' . $result1['fax'] . '</b></td>
					<td width="20%">REFFERNCE: &nbsp;</td>
					<td width="22%"><b>' . $result1['refference'] . '</b></td>
				</tr>
				<tr>
					<td width="15%">REMARK: &nbsp;</td>
					<td width="43%"><b>' . $result1['remark'] . '</b></td>
					<td width="20%">INVOICE NO.: &nbsp;</td>
					<td width="22%"><b>' . $result1['pvid'] . '</b></td>
				</tr>
				</table><div></div>';
    $pdf->writeHTML($html, false, false, true, false, '');

    $html = '';
    //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
    $html .= '<table cellpadding="1" cellspacing="1">
				<tr>
					<th width="5%">&nbsp;</th>
					<th width="80%" align="left">DESCRIPTION</th>
					<th width="15%" align="right">AMOUNT</th>
				</tr></table>';
    $pdf->SetFont('arial', '', 10);//20120704 ie6不显示//$pdf->SetFont('arialbd', '', 10);
    $pdf->writeHTML($html, false, false, true, false, '');
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

    $html = '';

    $pdf->SetFont('arial', '', 10);
// 新方法
    $total = 0;
//product 的個數
    $rtn_num = count($result2);


    for ($i = 0; $i < $rtn_num; $i++) {
        //為了將description數據庫中存儲的 \r\n 轉為<br />
        $result2[$i]['description'] = str_replace("\r\n", '<br />', $result2[$i]['description']);

        $html = '';
        $html .= '<table cellpadding="1" cellspacing="1">';
        $html .= '<tr>
				<td width="5%" align="left">' . ($i + 1) . '</td>
				<td width="80%" align="left">' . $result2[$i]['description'] . '</td>
				<td width="15%" align="right"><b>' . formatMoney($result2[$i]['amount']) . '</b></td>
			</tr>';
        $html .= '</table>';
        //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
        //$html .= ($i == count($result2)-1)?'':'<hr />';
        $pdf->writeHTML($html, false, false, true, false, '');
        $pdf->Ln(8);
        /*if($i != count($result2)-1){
            $pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());
        }*/
        $total += $result2[$i]['amount'];
    }
    $total = formatMoney($total);


    $html = '';
    $html .= '<table cellpadding="1" cellspacing="1"><hr />
				<tr>
				<td align="right" colspan="7"><b>CREDIT NOTE TOTAL (USD): </b></td>
				<td align="right"><b>' . $total . '</b></td>
				</tr>';

    $html .= '<tr><td>&nbsp;</td></tr>';

    $html .= '</table>';
    $pdf->SetFont('arial', '', 10);//20120704 ie6不显示//$pdf->SetFont('arialbd', '', 10);
    $pdf->writeHTML($html, false, false, true, false, '');

    $html = '';

    /* 例子
    $num=1220.01;
    echo fmoney($num);//结果：1,220.21
    echo umoney($num);
    //结果：ONE THOUSAND AND TWO HUNDRED TWENTY DOLLARS AND TWENTY-ONE CENTS ONLY
    echo umoney($num,"rmb");
    //结果：ONE THOUSAND AND TWO HUNDRED TWENTY YUAN AND TWENTY-ONE FEN ONLY
    */
    //為了將remarks數據庫中存儲的 \r\n 轉為<br />
    $remarks = str_replace("\r\n", '<br />', $result1['remarks']);
    //不知什麼原因textarea的所有文字前面的回車會被省略沒有保存如數據庫，所以這裡判斷文字中如果有回車，就在最前面加了一個
    if (strpos($result1['remarks'], "\r\n")) {
        $remarks = '<br />' . $remarks;
    }
    $html .= '<div align="left">REMARKS: ' . $remarks . '</div>';
    $pdf->SetFont('arial', '', 10);

    // output the HTML content
    $pdf->writeHTML($html, false, false, true, false, '');

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['cn_no'] . '.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


} else {
    die('Error(3)');
}
