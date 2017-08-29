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
        $image_file = K_PATH_IMAGES.'header_monthly_report.jpg';
        $this->Image($image_file, '', '', 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // create some HTML content
        $this->SetFont('times', '', 10);
        $this->Ln(45);
        $html = '';
        $html .= '<span align="right"><b>MONTHLY OPEN STYLES UPDATE ('.gmstrftime('%b').' '.date('Y').')</b><span/><hr height="2">';
        $this->writeHTML($html, false, false, true, false, '');
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


$rs2 = $mysql->q('select pid, photos from product where show_in_catalog = 1 and theme = 7 order by mod_date desc');
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
//$pdf->SetHeaderData('header.jpg', 180, '', '');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(10, 50, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

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
/*if(isset($result2)){
    $page_nums = (count($result2) <= 6)?1:(intval((count($result2)-6)/8)+2);
}*/



//$pdf->SetFont('arial', '', 10);
$pdf->SetFont('droidsansfallback', '', 10);

$html = '';
$html .= '<table align="center" cellpadding="1" cellspacing="1" style="background-color: white;">';
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
        $img_html = '';
        if(is_file('../../' . $pic_path_watermark . 'l_water_' . $result2[$i]['photos']) == true){
            $img_html = '<img src="/sys/'.$pic_path_watermark . 'l_water_'.$result2[$i]['photos'].'" align="middle" />';
        }

        //$img_html = '<div style="width:200px;height:150px;background-image:url(\'http://223.197.254.157/sys/'.$pic_path_small.'l_'.$result2[$i]['photos'].'\');">Lux Design Ltd</div>';
        //$img_html = '<div style="position:relative;"><img width="200px" height="150px" src="/sys/'.$pic_path_small . 'l_'.$result2[$i]['photos'].'" /><div style="position:absolute;width:200px;height:150px;z-indent:2;left:0;top:50px;color:grey;font-size:10;">Lux Design Ltd</div></div>';
        //$img_html = '<span style="background-image:url(\'/sys/'.$pic_path_small.'l_'.$result2[$i]['photos'].'\');">&nbsp;Lux Design Ltd&nbsp;</span>';
    }

    if($i % $col_num == 0){
        $html .= '<tr align="center">';
    }
    $sign++;
    //$img_html 为空的话也就显示个pid了，总不能什么都不显示吧
    $html .= '<td width="33.3333%">'.$img_html.'<br />'.$result2[$i]['pid'].'</td>';
    if($sign == $col_num){
        $html .= '</tr><br />';
        $sign = 0;
    }
}
if($sign != 0){
    $html .= '</tr>';
}
$html .= '</table>';
//echo $html;die();
$pdf->writeHTML($html, false, false, true, false, '');


// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('Generate_style_list'.time().'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+



?>
