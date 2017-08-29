<?php

/**
 * php create pdf
 * @package 
 * @abstract 
 * @author zjn
 * @since 2011.9
 */

require($_SERVER['DOCUMENT_ROOT'] . '\in7\global.php');

if(!isset($_SESSION['logininfo'])){
	die();	
}

require($_SERVER['DOCUMENT_ROOT'] . '\sys\in38\global_admin.php');
require_once('../../tcpdf/config/lang/eng.php');
require_once('../../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF {
	/*
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
	*/	
}


if(isset($_GET['id']) && $_GET['id'] != ''){
	$result = $mysql->qone('select * from bom where id = ?', $_GET['id']);
	$rs_m = $mysql->q('select * from bom_material where bom_id = ?', $_GET['id']);
	if($rs_m){
		$result_m = $mysql->fetch();	
	}
	$rs_t = $mysql->q('select * from bom_task where bom_id = ?', $_GET['id']);
	if($rs_t){
		$result_t = $mysql->fetch();	
	}	
		
	$g_process_array = explode('|', $result['g_process']);
	$electroplate_array = explode('|', $result['electroplate']);
	$electroplate_thick_array = explode('|', $result['electroplate_thick']);
	$other_array = explode('|', $result['other']);
	
	$all_array = array($g_process_array, $electroplate_array, $electroplate_thick_array, $other_array);

	$img_html = '';
	if($result['mysql_photo'] && $result['mysql_photo'] != ''){
		$arr = getimagesize(ROOT_DIR.'fty/'.$result['mysql_photo']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(250, 200, $pic_width, $pic_height);
		$img_html = '<img src="/fty/'.$result['mysql_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/>';
	}

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('ZJN');
	$pdf->SetTitle('LUX');
	$pdf->SetSubject('TCPDF');
	$pdf->SetKeywords('TCPDF');
	
	//mod 20120926 去掉顶上的公司标志
	// set my header img
	//$pdf->SetHeaderData('header.jpg', 180, '', '');
	
	// set header and footer fonts
	//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
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
	
	// create some HTML content
	$pdf->SetFont('times', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);
	$html = '<span align="right"><b>BOM</b><span/>';
	//$pdf->Line(15,99,195,99);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	// set font
	$pdf->SetFont('droidsansfallback', '', 10);

	// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
	// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
$html = '<hr height="2"><br />
<table width="100%" border="1">
  <tr>
    <td width="12%" height="45"><b>产品编号</b></td>
    <td width="48%" align="center">'.$result['g_id'].'</td>
    <td width="40%" rowspan="5" align="center" valign="middle">'.$img_html.'</td>
  </tr>
  <tr>
    <td height="45"><b>类别</b></td>
    <td width="48%" align="center">'.$result['g_type'].'</td>
  </tr>
  <tr>
    <td height="45"><b>底材用料</b></td>
    <td align="center" width="16%">'.$result['g_material'].'</td>
    <td width="6%"><b>尺码</b></td>
    <td colspan="3" align="center" width="26%">'.$result['g_size'].'</td>
  </tr>
  <tr>
    <td height="45"><b>成品总石数</b></td>
    <td align="center">'.$result['g_gem_num'].'</td>
    <td><b>铸件</b></td>
    <td colspan="3" align="center">'.$result['g_cast'].'</td>
  </tr>
  <tr>
    <td height="45"><b>电镀</b></td>
    <td align="center">'.$result['g_plating'].'</td>
    <td><b>重量</b></td>
    <td colspan="3" align="center">'.$result['g_weight'].'</td>
  </tr>
  <tr>
    <td width="12%"><b>物料编号</b></td>
    <td width="8%"><b>名称</b></td>
    <td width="8%"><b>规格颜色</b></td>
    <td width="6%"><b>类别</b></td>
    <td width="6%"><b>单价</b></td>
    <td width="10%"><b>个数/重量</b></td>
    <td width="10%"><b>备注</b></td>
    <td width="10%"><b>件工序号</b></td>
    <td width="10%"><b>工序名称</b></td>
    <td width="6%"><b>工价</b></td>
    <td width="6%"><b>工时</b></td>
    <td width="8%"><b>备注</b></td>
  </tr>
';

for($i = 0; $i < 13; $i++){
	if(isset($result_m[$i]['m_id']) && $result_m[$i]['m_id'] != ''){
		$html .= '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . $result_m[$i]['m_price'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . $result_m[$i]['m_remark'] . '</td>';
	}else{
		$html .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if(isset($result_t[$i]['t_id']) && $result_t[$i]['t_id'] != ''){
		$html .= '<td>' . $result_t[$i]['t_id'] . '</td><td>' . $result_t[$i]['t_name'] . '</td><td>' . $result_t[$i]['t_price'] . '</td><td>' . $result_t[$i]['t_time'] . '</td><td>' . $result_t[$i]['t_remark'] . '</td></tr>';			
	}else{
		$html .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	}
}
for($i = 13; $i < 23; $i++){
	if($i == 13){
		if(isset($result_m[$i]['m_id'])){
			$html .= '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . $result_m[$i]['m_price'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . $result_m[$i]['m_remark'] . '</td><td colspan="5" rowspan="8">&nbsp;</td></tr>';
		}else{
			$html .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan="5" rowspan="10"><table><tr><td width="69"><b>工序</b></td><td width="69"><b>电镀</b></td><td width="69"><b>电镀厚度</b></td><td width="69"><b>其他</b></td></tr>';
			for($j = 0; $j < $select_max; $j++){
				$html .= '<tr>';
				for($k = 0; $k < 4; $k++){
					$html .= '<td>';
					if(isset($allinarray[$k][$j][0])){
						if(in_array($allinarray[$k][$j][0], $all_array[$k])){
							$html .= '■'.$allinarray[$k][$j][0]/*.'√'*/;
						}else{
							$html .= '□'.$allinarray[$k][$j][0];
						}	
					}
					$html .= '</td>';	
				}
				$html .= '</tr>';
			}
			$html .= '</table></td></tr>';
		}
	}else{
		if(isset($m_id_array[$i])){
			$html .= '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . $result_m[$i]['m_price'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . $result_m[$i]['m_remark'] . '</td></tr>';
		}else{
			$html .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
		}		
	}
}
$html .= '<tr><td colspan="12"><b>电镀人工价</b>：'.$result['p_plate'].'&nbsp;&nbsp;<b>其他成本</b>：'.$result['p_other'].'&nbsp;&nbsp;<b>利润</b>：'.$result['p_profit'].'&nbsp;&nbsp;<b>合计</b>：'.$result['p_total'].' </td></tr>';
$html .= '</table>';
$html .= '<p><b>经手人</b>：'.$result['created_by'].'&nbsp;&nbsp;&nbsp; <b>审核</b>：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>时间：</b>'.$result['g_time'].'</p>';

// output the HTML content
$pdf->writeHTML($html, false, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($result['g_id'].'.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+


}else{
	die('系統故障(2)');	
}