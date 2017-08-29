<?php
/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['luxcraftlogininfo'])){
    die();
}

require($_SERVER['DOCUMENT_ROOT'] . '/luxcraft/in38/global_admin.php');
require_once('../../tcpdf/config/lang/eng.php');
require_once('../../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        //20131112 去掉旧的头部图片，等待换新的头部图片
        //20140226 pos 用的是新的header图片
        // Logo
        $image_file = K_PATH_IMAGES.'header_pos.jpg';
        $this->Image($image_file, '', '', '', 40, 'JPG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
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

if(isset($_POST['start_date']) && $_POST['start_date'] != ''){

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ZJN');
    $pdf->SetTitle('LUX');
    $pdf->SetSubject('TCPDF');
    $pdf->SetKeywords('TCPDF');

    // set my header img
    $pdf->SetHeaderData('header_pos.jpg', 180, '', '');

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    //20140226 因换了header图片，所以把高度调大了
    $pdf->SetMargins(PDF_MARGIN_LEFT, 55/*PDF_MARGIN_TOP*/, PDF_MARGIN_RIGHT);
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

    $pdf->SetFont('arial', '', 10);


    $file_name = '';
    if(isset($_POST['end_date']) && $_POST['end_date'] != ''){
        $rs = $mysql->q('select * from sales_invoice where invoice_date >= ? and invoice_date < ?', $_POST['start_date'].' 00:00:00', $_POST['end_date'].' 23:59:59');
        $file_name = '('.$_POST['start_date'].' ---- '.$_POST['end_date'].')';
    }else{
        $rs = $mysql->q('select * from sales_invoice where invoice_date  = ?', $_POST['start_date']);
        $file_name = '('.$_POST['start_date'].')';
    }
    if($rs){
        $rtn = $mysql->fetch();

        $html = '<div style="text-align: center">Daily Sales Report</div><div style="text-align: center;">'.$file_name.'</div><br />
                <table align="left" cellpadding="1" cellspacing="1" border="1">
                <tr>
                    <th>Invoice NO.</th>
                    <th>Item NO.</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                    <th>Pay Method</th>
                    <th>Salesman</th>
                </tr>';

        foreach($rtn as $v){
            $rs = $mysql->q('select * from sales_invoice_item where sales_vid = ?', $v['sales_vid']);
            if($rs){
                $item_rtn = $mysql->fetch();
                foreach($item_rtn as $w){
                    $html .= '<tr align="center">
                                <td>'.$w['sales_vid'].'</td>
                                <td>'.$w['pid'].'</td>
                                <td>'.$w['qty'].'</td>
                                <td>'.$w['price'].'</td>
                                <td>'.$w['discount'].'</td>
                                <td>'.($w['qty']*$w['price'] - $w['discount']).'</td>
                                <td>'.$v['payment_method'].'</td>
                                <td>'.$v['created_by'].'</td>
                              </tr>';
                }
            }
        }
        $html .= '</table>';
        $pdf->writeHTML($html, false, false, true, false, '');

        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('daily_sales_report'.$file_name.'.pdf', 'I');
    }else{
        die('No Invoice Data');
    }
}else{
    die('Need Start Date');
}