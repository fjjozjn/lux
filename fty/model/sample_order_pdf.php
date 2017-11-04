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
require_once('../../tcpdf/config/lang/eng.php');
require_once('../../tcpdf/tcpdf.php');

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

if(isset($_GET['so_no']) && $_GET['so_no'] != ''){
	//判断是否有访问权限
	//6.14修改为无需权限判断
	/*
	if($_SESSION['logininfo']['aName'] != 'KEVIN' && $_SESSION['logininfo']['aName'] != 'zjn'){
		$rtn = $mysql->qone("select created_by from purchase where pcid = ?", $_GET['pcid']);
		if($rtn['created_by'] != $_SESSION['logininfo']['aNameChi']){
			$rtn1 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $rtn['created_by']);
			$rtn2 = $mysql->qone('select AdminLuxGroup from tw_admin where AdminName = ?', $_SESSION['logininfo']['aName']);
			if( ($rtn1['AdminLuxGroup'] == NULL && $rtn2['AdminLuxGroup'] == NULL) || ($rtn1['AdminLuxGroup'] != $rtn2['AdminLuxGroup']) ){
				die('Without Permission To Access');
			}
		}
	}*/
	
	$result = $mysql->qone('select * from sample_order where so_no = ?', $_GET['so_no']);
	if(!$result){
		die('Error(1)');	
	}
	$creation_date = date('Y-m-d', strtotime($result['creation_date']));
	$etd = date('Y-m-d', strtotime($result['etd']));

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
	$pdf->setFooterFont(false);
	
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

    //mod 20171104 加未审核水印
    if ($result['s_status'] == '(D)') {
        $pdf->Image('../../images/draft.gif', 55, 70, 100, 100, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }

	// create some HTML content
	$pdf->SetFont('droidsansfallback', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);

    $title = '';
    if(strpos($_GET['so_no'], 'REV')){
        $title = '改板单';
    }else{
        $title = '样板订单';
    }

	$html = '<span align="right"><b>'.$title.'</b><span/>';
	//畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了
	//$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	$pdf->SetFont('droidsansfallback', '', 10);
	$html = '';

	$html .='<hr height="2"><table width="100%" cellpadding="2" cellspacing="2">
		<tr>
			<td width="15%">致：</td>
			<td width="35%">'.$result['send_to'].'</td>
			<td width="20%">编号：</td>
			<td width="30%">'.$result['so_no'].'</td>
		</tr>
		<tr>
			<td width="15%">收件人：</td>
			<td width="35%">'.$result['attention'].'</td>
			<td width="20%">客户：</td>
			<td width="30%">'.$result['customer'].'</td>
		</tr>
		<tr>
			<td width="15%">参考：</td>
			<td width="35%">'.$result['reference'].'</td>
			<td width="20%">要求出货日期：</td>
			<td width="30%">'.$etd.'</td>
		</tr>  
		<tr>
			<td width="15%">备注：</td>
			<td width="35%">'.$result['remark'].'</td>
			<td width="20%">日期：</td>
			<td width="30%">'.$creation_date.'</td>
		</tr>  
		<tr>
			<td width="15%"></td>
			<td width="35%"></td>
			<td width="20%">负责同事：</td>
			<td width="30%">'.$result['created_by'].'</td>
		</tr> 			  
	</table><hr />';
	//$pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());
	//$html = '';
	$html .= '<table cellpadding="2" cellspacing="2">
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width="80">1）影印图：</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['photo_page_num'].'</td>
			<td width="90">页， 连此页：</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['page_total'].'</td>
			<td>页</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>		
		<tr>
			<td width="40">2）共</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['product_total'].'</td>
			<td width="65">款，每款</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['color_total'].'</td>
			<td width="100">色。每款每色：</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['product_each_num'].'</td>
			<td width="140">件， 连深圳留底板各：</td>
			<td style="border-bottom: 1 solid black" width="50" align="center">'.$result['product_num'].'</td>
			<td>件</td> 
		</tr>
	</table>';

	$html .= '<table cellpadding="2" cellspacing="2">
		<tr>
			<td></td>
			<td></td>
			<td width="80"></td>
			<td width="80"></td>
			<td width="80"></td>
			<td width="180"></td>
		</tr>	
		<tr>
			<td>3）细节要求：</td>
			<td></td>
			<td width="80"></td>
			<td width="80"></td>
			<td width="80"></td>
			<td width="180"></td>
		</tr>
		<tr>
			<td width="20"></td>
			<td width="170">a.&nbsp;&nbsp;单内凡改板款做公司办：</td>
			<td>'.(($so_select1[0] == $result['is_change'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'是</td>
			<td>'.(($so_select1[1] == $result['is_change'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'否'.'</td>
		</tr>
		<tr>
			<td></td>
			<td>b.&nbsp;&nbsp;金色系列：</td>
			<td>'.(($so_select2[0] == $result['select_gold'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'12K金</td>
			<td>'.(($so_select2[1] == $result['select_gold'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'14K金</td>
			<td>'.(($so_select2[2] == $result['select_gold'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'其他</td>
			<td><U>'.$result['gold_other'].'</U></td>
		</tr>
		<tr>
			<td></td>
			<td>c.&nbsp;&nbsp;光金是否加保护层：</td>
			<td>'.(($so_select3[0] == $result['select_is_layer'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'是</td>
			<td>'.(($so_select3[1] == $result['select_is_layer'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'否</td>
			<td>'.(($so_select3[2] == $result['select_is_layer'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'其他</td>
			<td><U>'.$result['layer_other'].'</U></td>
		</tr>
		<tr>
			<td></td>
			<td>d.&nbsp;&nbsp;是否做无叻电镀：</td>
			<td>'.(($so_select1[0] == $result['select_is_electroplate'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'是</td>
			<td>'.(($so_select1[1] == $result['select_is_electroplate'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'否</td>
		</tr>
		<tr>
			<td></td>
			<td>e.&nbsp;&nbsp;是否做无铅：</td>
			<td>'.(($so_select1[0] == $result['select_is_lead'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'是</td>
			<td>'.(($so_select1[1] == $result['select_is_lead'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'否</td>
		</tr>	
		<tr>
			<td></td>
			<td>f.&nbsp;&nbsp;耳针配套耳塞：</td>
			<td>'.(($so_select4[0] == $result['select_earrings'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'蝴蝶塞</td>
			<td>'.(($so_select4[1] == $result['select_earrings'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'子弹塞</td>
			<td>'.(($so_select4[2] == $result['select_earrings'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'飞碟塞</td>
			<td>'.(($so_select4[3] == $result['select_earrings'])?'<img src="../../images/gou.jpg" width="12" />':'<img src="../../images/kuan.jpg" width="12" />').'透明耳塞</td>
		</tr>					
	</table><br />';

	$html .= '<table cellpadding="2" cellspacing="2">
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>	
		<tr>
			<td>4）包装：</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width="20"></td>
			<td width="80">a.&nbsp;&nbsp;包装卡：</td>
			<td style="border-bottom: 1 solid black" width="180" align="left">'.$result['packaging_card'].'</td>
			<td width="97">b.&nbsp;&nbsp;戒子标签：</td>
			<td style="border-bottom: 1 solid black" width="180" align="left">'.$result['ring_tag'].'</td>
		</tr>
		<tr>
			<td width="20"></td>
			<td width="95">c.&nbsp;&nbsp;戒子尺码：</td>
			<td colspan="3" style="border-bottom: 1 solid black" width="443" align="left">'.$result['ring_size'].'</td>
		</tr>
		<tr>
			<td width="20"></td>
			<td width="100">d.&nbsp;&nbsp;包装要求：</td>
			<td colspan="3">'.str_replace("\r\n", '<br />', $result['packaging_require']).'</td>
		</tr>				
	</table><br />';

	$html .= '<table cellpadding="2" cellspacing="2">
		<tr>
			<td></td>
		</tr>	
		<tr>
			<td>5）其他：</td>
		</tr>
		<tr>
			<td>'.str_replace("\r\n", '<br />', $result['others']).'</td>
		</tr>
	</table>';


	// output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');
	
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output($result['so_no'].'.pdf', 'I');
	
	//============================================================+
	// END OF FILE                                                
	//============================================================+
	

}else{
	die('Error(3)');	
}
?>
