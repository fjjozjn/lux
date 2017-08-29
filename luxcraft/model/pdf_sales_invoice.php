<?php

//没有背景图，为了省打印油墨

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
        //$image_file = $_SERVER['DOCUMENT_ROOT'] . '/luxcraft/images/header_pos.jpg';
        //$this->Image($image_file, '', '', '', 32, 'JPG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);

        /*设置背景图片方法一
        // -- set new background ---
        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->AutoPageBreak;
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set bacground image
        $img_file = $_SERVER['DOCUMENT_ROOT'] . '/luxcraft/images/for_printing_bg.jpg';
        $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();
        */
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('arial', 'I', 10);
        // Page number
        $this->Cell(200, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

        //20141119 在上半部分也加上页脚
        // Position at 15 mm from bottom
        $this->SetY(134);
        // Set font
        $this->SetFont('arial', 'I', 10);
        // Page number
        $this->Cell(200, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

if(isset($_GET['sales_vid']) && $_GET['sales_vid'] != ''){

    //20150325 session存在则说明是从add页面打开，此时还没有生成订单，所以要刷新一次页面
    if(isset($_SESSION['luxcraftlogininfo']['open_pdf'])){
        echo 'loading...';
        unset($_SESSION['luxcraftlogininfo']['open_pdf']);
        if(!isset($_SESSION['luxcraftlogininfo']['open_pdf'])){
            echo "<script language=JavaScript> location.replace(location.href); </script>";
        }
    }

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

    $result1 = $mysql->qone('select * from sales_invoice where sales_vid = ?', $_GET['sales_vid']);
    if(!$result1){
        die('Error(1)');
    }
    $result1['mod_date'] = date('Y/m/d', strtotime($result1['mod_date']));
    //20160501
    $customer_name = 'Walk-in';
    if($result1['vip_phone']){
        $vip = $mysql->qone('select * from luxcraft_membership where phone = ?', $result1['vip_phone']);
        $customer_name = $vip['title'].' '.$vip['family_name'];
    }
    //20130828 改为没找到item，不die了
    $rs2 = $mysql->q('select * from sales_invoice_item where sales_vid = ?', $_GET['sales_vid']);
    if($rs2){
        $result2 = $mysql->fetch();
        for($i = 0; $i < count($result2); $i++){
            $rtn = $mysql->qone('select type from product where pid = ?', $result2[$i]['pid']);
            $result2[$i]['description'] = $rtn['type'];
        }
    }


    //20141118 加pdf分页显示
    //第一页显示的item个数（有表头）
    $first_page_item_num = 10;
    //除第一页外的显示item个数（无表头）
    $other_page_item_num = 13;

    $result_item = array();
    $index = 0;
    $i = 1;
    $j = 1;
    foreach($result2 as $v){
        if($i <= $first_page_item_num){
            $result_item[$index][] = $v;
            if($i == $first_page_item_num){
                $index++;
            }
            $i++;
        }else{
            if($j <= $other_page_item_num){
                $result_item[$index][] = $v;
                if($j == $other_page_item_num){
                    $index++;
                    $j = 0;
                }
            }
            $j++;
        }
    }
    //fb($result_item);die();



    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ZJN');
    $pdf->SetTitle('LUX');
    $pdf->SetSubject('TCPDF');
    $pdf->SetKeywords('TCPDF');

    // set my header img
    //$pdf->SetHeaderData('header_pos.jpg', 180, '', '');

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    //20140226 因换了header图片，所以把高度调大了
    //$pdf->SetMargins(PDF_MARGIN_LEFT, 45/*PDF_MARGIN_TOP*/, PDF_MARGIN_RIGHT);
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

    $result_i = 1;
    $total_amount = 0;
    $style = 'border-bottom-style:solid;';
    foreach($result_item as $result2){

        // add a page
        $pdf->AddPage();//怎麼能自動分頁？這樣我就不用AddPage了，每頁頂上的表頭問題也能解決
/*
        //设置背景图片方法二
// remove default header
        $pdf->setPrintHeader(false);
// -- set new background ---
// get the current page break margin
        $bMargin = $pdf->getBreakMargin();
// get current auto-page-break mode
        $auto_page_break = $pdf->getAutoPageBreak();
// disable auto-page-break
        $pdf->SetAutoPageBreak(false, 0);
// set bacground image
        $img_file = $_SERVER['DOCUMENT_ROOT'] . '/luxcraft/images/for_printing_bg.jpg';
        $pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
// restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
// set the starting point for the page content
        $pdf->setPageMark();
    */
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
        //$html = '<span align="center"><b>Invoice</b><span/>';
        //$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
        //$pdf->writeHTML($html, true, false, true, false, '');
        //$pdf->Ln(1);


        $pdf->SetFont('arial', '', 8);
        $html = '';

        $html .= '<br /><br />';
        if($result_i == 1){
            //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
            $html .= '<table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="20%">Customer : &nbsp;</td>
					<td width="50%">'.$customer_name.'</td>
					<td width="13%">Inv NO. : &nbsp;</td>
					<td width="17%">'.$result1['sales_vid'].'</td>
				</tr>
				<tr>
					<td>Shop : &nbsp;</td>
					<td>'.$result1['wh_name'].'</td>
					<td>Date : &nbsp;</td>
					<td>'.$result1['mod_date'].'</td>
				</tr>
				<tr>
					<td>Payment Method : &nbsp;</td>
					<td>'.$result1['payment_method'].'</td>
					<td>Staff : &nbsp;</td>
					<td>'.$result1['created_by'].'</td>
				</tr>
				<tr>
					<td>Remark : &nbsp;</td>
					<td>'.$result1['remark'].'</td>
					<td></td>
					<td></td>
				</tr>
				</table>';
            //$pdf->writeHTML($html, false, false, true, false, '');
        }

        //20130828 要result2有东西，才显示下面，不然下面<table>标签不全，出PDF会报错
        if($rs2){
            //$html = '';
            //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
            //20141026 table frame属性这里不能用
            $html .= '<table style="border-top-style:solid;border-bottom-style:solid;" align="center" cellpadding="1"
 cellspacing="1">
                    <tr>
                        <th width="20%">Item #</th>
                        <th width="25%">Desc</th>
                        <th width="16%">Qty</th>
                        <th width="17%">Unit Price</th>
                        <th width="17%">Amount</th>
                        <th width="5%"></th>
                    </tr></table>';
            //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

            $html .= '<table style="'.( ($result_i == count($result_item))?$style:'' ).'" align="center" cellpadding="1" cellspacing="1">';

            for($i = 0; $i < count($result2); $i++){
                $total_amount += $result2[$i]['price']*$result2[$i]['qty']-$result2[$i]['discount'];

                $html .= '<tr>
                        <td width="20%" align="left">'.$result2[$i]['pid'].'</td>
                        <td width="25%">'.$result2[$i]['description'].'</td>
                        <td width="16%">'.$result2[$i]['qty'].'</td>
                        <td width="17%" align="right">'.$result2[$i]['price'].'</td>
                        <td width="17%" align="right">'.number_format($result2[$i]['price']*$result2[$i]['qty'],
                    2).'</td>
                        <td width="5%"></td>
                    </tr>';
            }
            $html .= '</table>';

            if($result_i == count($result_item)){
                $html .= '<table align="center" cellpadding="1" cellspacing="1">';
                if($result1['discount'] != 0){
                    $html .='<tr>
                        <td width="20%"></td>
                        <td width="25%"></td>
                        <td width="16%"></td>
                        <td width="17%" align="right">Discount :</td>
                        <td width="17%" align="right">'.number_format($result1['discount'], 2).'</td>
                        <td width="5%"></td>
                    </tr>';
                }
                $html .= '<tr>
                        <td width="20%"></td>
                        <td width="25%"></td>
                        <td width="16%"></td>
                        <td width="17%" align="right">Total :</td>
                        <td width="17%" align="right">'.number_format($total_amount, 2).'</td>
                        <td width="5%"></td>
                    </tr>
                </table>';
            }
            //$pdf->writeHTML($html, false, false, true, false, '');
            $pdf->writeHTMLCell('', '', '', 45, $html, 0, 0, false, true, '', true);

            //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

            //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
            //$html .= ($i == count($result2)-1)?'':'<hr />';
            //$pdf->Ln(2);
        }


        //copy
        $html = '';

        $html .= '<br /><br />';
        if($result_i == 1){
            //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
            $html .= '<table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="20%">Customer : &nbsp;</td>
					<td width="50%">'.$customer_name.'</td>
					<td width="13%">Inv NO. : &nbsp;</td>
					<td width="17%">'.$result1['sales_vid'].'</td>
				</tr>
				<tr>
					<td>Shop : &nbsp;</td>
					<td>'.$result1['wh_name'].'</td>
					<td>Date : &nbsp;</td>
					<td>'.$result1['mod_date'].'</td>
				</tr>
				<tr>
					<td>Payment Method : &nbsp;</td>
					<td>'.$result1['payment_method'].'</td>
					<td>Staff : &nbsp;</td>
					<td>'.$result1['created_by'].'</td>
				</tr>
				<tr>
					<td>Remark : &nbsp;</td>
					<td>'.$result1['remark'].'</td>
					<td></td>
					<td></td>
				</tr>
				</table>';
            //$pdf->writeHTML($html, false, false, true, false, '');
        }

        //20130828 要result2有东西，才显示下面，不然下面<table>标签不全，出PDF会报错
        if($rs2){
            //$html = '';
            //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
            $html .= '<table style="border-top-style:solid;border-bottom-style:solid;" align="center" cellpadding="1" cellspacing="1">
                    <tr>
                        <th width="20%">Item #</th>
                        <th width="25%">Desc</th>
                        <th width="16%">Qty</th>
                        <th width="17%">Unit Price</th>
                        <th width="17%">Amount</th>
                        <td width="5%"></td>
                    </tr></table>';
            //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

            $html .= '<table style="'.( ($result_i == count($result_item))?$style:'' ).'" align="center" cellpadding="1" cellspacing="1">';

            for($i = 0; $i < count($result2); $i++){

                $html .= '<tr>
                        <td width="20%">'.$result2[$i]['pid'].'</td>
                        <td width="25%">'.$result2[$i]['description'].'</td>
                        <td width="16%">'.$result2[$i]['qty'].'</td>
                        <td width="17%">'.$result2[$i]['price'].'</td>
                        <td width="17%" align="right">'.number_format($result2[$i]['price']*$result2[$i]['qty'],
                    2).'</td>
                        <td width="5%"></td>
                    </tr>';
            }
            $html .= '</table>';

            if($result_i == count($result_item)){
                $html .= '<table align="center" cellpadding="1" cellspacing="1">';
                if($result1['discount'] != 0){
                    $html .= '<tr>
                        <td width="20%"></td>
                        <td width="25%"></td>
                        <td width="16%"></td>
                        <td width="17%" align="right">Discount :</td>
                        <td width="17%" align="right">'.number_format($result1['discount'], 2).'</td>
                        <td width="5%"></td>
                    </tr>';
                }
                $html .= '<tr>
                        <td width="20%"></td>
                        <td width="25%"></td>
                        <td width="16%"></td>
                        <td width="17%" align="right">Total :</td>
                        <td width="17%" align="right">'.number_format($total_amount, 2).'</td>
                        <td width="5%"></td>
                    </tr>
                </table>';
            }
            //$pdf->writeHTML($html, false, false, true, false, '');
            $pdf->writeHTMLCell('', '', '', 195, $html, 0, 0, false, true, '', true);

            //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());

            //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
            //$html .= ($i == count($result2)-1)?'':'<hr />';
            //$pdf->Ln(2);
        }


        //20160430 获取店铺信息
        //20160430 不用图片，改用数据库里的的内容
        $pdf->SetFont('arial', '', 10);
        $pdf->writeHTMLCell(150, '', '', 123, '<font color="#808080">Luxcraft Shop:</font>', 0, 0, false, true, '',
            true);
        $pdf->writeHTMLCell(150, '', '', 271, '<font color="#808080">Luxcraft Shop:</font>', 0, 0, false, true, '',
            true);
        $shop = '';
        $rs_shop = $mysql->q('select * from warehouse where type = ?', 'Shop');
        if($rs_shop){
            $result_shop = $mysql->fetch();

            //$shop .= '<table align="left" cellpadding="1" cellspacing="1">';
            for($i = 1; $i <= count($result_shop); $i++){
                //20160430 //只显示TST
                if($result_shop[$i]['wh_name'] == 'TST'){
//                    if($i%2 != 0){
//                        $shop .= '<tr>';
//                    }
//                    $shop .= '<td>'.$result_shop[$i]['address'].' '.$result_shop[$i]['address_en'].'</td>';
//                    if($i%2 == 0){
//                        $shop .= '</tr>';
//                    }
                    $shop .= '<font color="#808080">'.$result_shop[$i]['address'].'<br />'
                        .$result_shop[$i]['address_en'].'</font>';
                }
            }
            //$shop .= '</table>';
        }
        //fb($shop);die('@');
        //$pdf->writeHTMLCell('', '', '', 100, $shop, 0, 0, false, true, '', true);
        //$pdf->writeHTMLCell('', '', '', 300, $shop, 0, 0, false, true, '', true);

        //$shop = '<table width="100%"><tr><td><img src="/luxcraft/images/shop_info.png" /></td></tr></table>';
        $pdf->SetFont('droidsansfallback', '', 8);
        $pdf->writeHTMLCell(150, '', '', 127, $shop, 0, 0, false, true, '', true);
        $pdf->writeHTMLCell(150, '', '', 275, $shop, 0, 0, false, true, '', true);

        $result_i++;
    }

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['sales_vid'].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


}else{
    die('Error(3)');
}