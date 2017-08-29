<?php
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['logininfo'])){
    die('Please login!');
}
if(!isset($_GET['wh_name']) || $_GET['wh_name'] == ''){
    die('Need warehouse ID!');
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
//$pdf->AddPage();//怎麼能自動分頁？這樣我就不用AddPage了，每頁頂上的表頭問題也能解決

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
$pdf->SetFont('times', '', 20);
//div的高度不可調，用span方便多了！！！
//找到調高度的方法了
$pdf->Ln(1);
$html = '<span align="right"><b>WAREHOUSE</b><span/>';
//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(1);

$pdf->SetFont('arial', '', 8);
$html = '';

//20130723 去掉必须输入时间的设定
//if ( strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date']) ){
//$sql = 'select pvid, mark_date, printed_by, send_to, reference, istatus from proforma where mark_date between ? and ?';
$temp_table = ' warehouse_item_unique w left join product p on w.pid = p.pid';

$where_sql = " AND w.qty > 0 AND w.wh_name = '".$_GET['wh_name']."'";
if (strlen(@$_SESSION['search_criteria']['pid'])){
    $where_sql.= " AND w.pid Like '%".$_SESSION['search_criteria']['pid'].'%\'';
}
if (strlen(@$_SESSION['search_criteria']['type'])){
    $where_sql.= " AND p.type Like '%".$_SESSION['search_criteria']['type'].'%\'';
}
if (strlen(@$_SESSION['search_criteria']['start_date'])){
    if (strlen(@$_SESSION['search_criteria']['end_date'])){
        $where_sql.= " AND w.in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
    }else{
        $where_sql.= " AND w.in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
    }
}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
    $where_sql.= " AND w.in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
}

$where_sql .= ' ORDER BY w.pid';
$list_field = ' w.pid, w.qty, w.photo ';
$start_row = 0;
//默认值100000，相当于一页显示无限多条的记录了
$end_row = 100000;

$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
//$rs = $mysql->q($sql, $_SESSION['search_criteria']['start_date']." 00:00:00", $_SESSION['search_criteria']['end_date']." 23:59:59", (strlen(@$_SESSION['search_criteria']['user']))?'%'.$_SESSION['search_criteria']['user'].'%':'%'.$_SESSION['search_criteria']['status'].'%', '%'.$_SESSION['search_criteria']['status'].'%');
if($info){
    //$rtn = $mysql->fetch();
    $rtn = $mysql->fetch(0, 1);

    $header = '<hr height="2"><br/><br/><table border="1" align="center">
		<tr bgcolor="#EEEEEE"> 
			<th>Photo</th>
			<th>Warehouse</th>
			<th>Item code</th>
			<th>Qty</th>
			<th>Photo</th>
			<th>Warehouse</th>
			<th>Item code</th>
			<th>Qty</th>
		</tr>';

    $end .= "</table>";

    //20130726 因为文字内容并不多，所以只要控制了图片的高度，每页item的个数也能限定了
    //13行差不多满一页
    $per_page_item_num = 26;//每行两个，所以是13行


    $index = 1;
    $page = 1;
    $num = count($rtn);
    foreach($rtn as $v){

        if($index % 2 != 0){
            $html .= '<tr>';
        }

        $img_html = '';
        if($v['photo'] != ''){
            if (is_file('../../' . $pic_path_com . $v['photo']) == true) {
                $mid_photo = 'm_' . $v['photo'];
                //縮小的圖片不存在才進行縮小操作
                if (!is_file('../../' . $pic_path_small . $mid_photo) == true) {
                    makethumb('../../' . $pic_path_com . $v['photo'], '../../' . $pic_path_small . $mid_photo, 'm');
                }
                //寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用m_的圖片，分辨率高點
                $img_html = '<img src="/sys/'.$pic_path_small . 'm_'.$v['photo'].'" align="middle" />';
            }else{
                $img_html = '<img src="/images/nopic.gif" align="middle" />';
            }
        }else{
            $img_html = '<img src="/images/nopic.gif" align="middle" />';
        }
        $html .= '<td>'.$img_html.'</td>';
        $html .= '<td>'.$_GET['wh_name'].'</td>';
        $html .= '<td>'.$v['pid'].'</td>';
        $html .= '<td>'.$v['qty'].'</td>';

        //最后一行只有一个item的情况下也要加</tr>，否则报错
        if($num == $index){
            $html .= '</tr>';
        }else{
            if($index % 2 == 0){
                $html .= '</tr>';
            }
        }

        if($index == ($page * $per_page_item_num)){
            $pdf->AddPage();
            $pdf->writeHTML($header.$html.$end, false, false, true, false, '');
            $html = '';
            $page++;
        }

        $index++;
    }

}else{
    die('No data !');
}
//echo $html;
// output the HTML content
$pdf->AddPage();
//print_r($html);die();
$pdf->writeHTML($header.$html.$end, false, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('warehouse_item.pdf', 'I');
/*}else{
    die('Please select start date and end date!');
}*/

