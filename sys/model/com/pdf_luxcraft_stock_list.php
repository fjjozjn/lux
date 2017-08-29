<?php
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

//以防未登入就直接访问 
if(!isset($_SESSION['logininfo'])){
    die('');
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

//判断是否有访问权限
/*if(!isSysAdmin()){
    $rtn = $mysql->qone("select printed_by from proforma where pvid = ?", $_GET['pvid']);
    if($rtn['printed_by'] != $_SESSION['logininfo']['aName']){
        $rtn1 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $rtn['printed_by']);
        $rtn2 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
        if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
            die('Without Permission To Access');
        }
    }
}*/

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

//$pdf->SetFont('droidsansfallback', '', 10);
$pdf->SetFont('arial', '', 10);

//warehouse
$wh_rs = $mysql->q('select wh_name from warehouse order by type');
if($wh_rs){
    $wh_rtn = $mysql->fetch();

    $html = '';
    $html .= '<hr height="2"><br/><br/><table border="1" align="center">
		<tr bgcolor="#EEEEEE">
			<th>Item NO.</th>
			<th>Image</th>
			<th>Selling Price</th>';

    foreach($wh_rtn as $w){
        $html .= '<th>'.$w["wh_name"].'</th>';
    }

    $html .='<th>Item NO.</th>
			<th>Image</th>
			<th>Selling Price</th>';

    foreach($wh_rtn as $w){
        $html .= '<th>'.$w["wh_name"].'</th>';
    }

    $html .= '</tr>';

    /********** 数据部分 ***********/
    //只显示 exclusive to Luxcraft (即LUX)
    $all_info = array();
    $rs1 = $mysql->q('select pid, photos, suggested_price from product where exclusive_to = ?', 'LUX');
    if($rs1){
        $result1 = $mysql->fetch();

        for($i = 0; $i < count($result1); $i++){

            if(($i+1) % 2 != 0){
                $html .= '<tr>';
            }

            $rs2 = $mysql->q('select wh_name, qty from warehouse_item_unique where pid = ?', $result1[$i]['pid']);
            if($rs2){
                $result2 = $mysql->fetch();
                foreach($result2 as $h){
                    $result1[$i][$h['wh_name']] += $h['qty'];
                }
            }

            if(isset($result1[$i]['photos']) && $result1[$i]['photos'] != ''){
                if (is_file('../../' . $pic_path_com . $result1[$i]['photos']) == true) {
                    /*
                    $arr = getimagesize('../../' . $pic_path_com . $result2[$i]['photos']);
                    $pic_width = $arr[0];
                    $pic_height = $arr[1];
                    $image_size = getimgsize(115, 85, $pic_width, $pic_height);
                    $img_html = '<img src="/sys/'.$pic_path_com . $result2[$i]['photos'].'" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/>';
                    */

                    //壓縮圖片
                    //$result2[$i]['photos']是原來的， $mid_photo 是縮小後的
                    //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
                    $mid_photo = 'm_' . $result1[$i]['photos'];
                    //縮小的圖片不存在才進行縮小操作
                    if (!is_file('../../' . $pic_path_small . $mid_photo) == true) {
                        makethumb('../../' . $pic_path_com . $result1[$i]['photos'], '../../' . $pic_path_small . $mid_photo, 'm');
                    }
                    //寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用m_的圖片，分辨率高點
                    $result1[$i]['photos'] = '<img src="/sys/'.$pic_path_small . 'm_'.$result1[$i]['photos'].'" align="middle" />';
                }else{
                    $result1[$i]['photos'] = '';
                }
            }

            $html .= '<td>'.$result1[$i]['pid'].'</td>';
            $html .= '<td>'.$result1[$i]['photos'].'</td>';
            $html .= '<td>'.$result1[$i]['suggested_price'].'</td>';
            foreach($wh_rtn as $r){
                $html .= '<td>'.((isset($result1[$i][$r['wh_name']]) && $result1[$i][$r['wh_name']] != '')?$result1[$i][$r['wh_name']]:0).'</td>';
            }

            //最后一行只有一个item的情况下也要加</tr>，否则报错
            if(count($result1) == ($i+1)){
                $html .= '</tr>';
            }else{
                if(($i+1) % 2 == 0){
                    $html .= '</tr>';
                }
            }
        }

    }else{
        die('Error(1)');
    }
}else{
    die('Error(0)');
}

$html .= '</table>';
$pdf->writeHTML($html, false, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('luxcraft_stock_list.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+