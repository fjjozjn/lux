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
		$image_file = K_PATH_IMAGES.'header.jpg';
		$this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}	
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('droidsansfallback', 'I', 10);
		// Page number
		$this->Cell(200, 10, '第 '.$this->getAliasNumPage().' 頁，共 '.$this->getAliasNbPages().' 頁', 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}	
}

if(isset($_GET['pcid']) && $_GET['pcid'] != ''){
	$result1 = $mysql->qone('select * from purchase where pcid = ?', $_GET['pcid']);
	if(!$result1){
		die('Error(1)');	
	}
	// \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
	$send_to = str_replace("\r\n", '<br />', $result1['send_to']);
	$make_date = date('Y/m/d', strtotime($result1['mark_date']));
	$expected_date = date('Y/m/d', strtotime($result1['expected_date']));
	$rs2 = $mysql->q('select * from purchase_item where pcid = ?', $_GET['pcid']);
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
	if(isset($result2)){
		$page_nums = (count($result2) <= 6)?1:(intval((count($result2)-6)/8)+2);
	}
	// create some HTML content
	$pdf->SetFont('droidsansfallback', '', 20);
	//div的高度不可調，用span方便多了！！！
	//找到調高度的方法了
	$pdf->Ln(1);
	$html = '<span align="right"><b>採購訂單</b><span/>';
	//畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了
	//$pdf->Line(15,85,195,85);//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
	$pdf->writeHTML($html, true, false, true, false, '');
	//$pdf->Ln(1);
	
	$pdf->SetFont('droidsansfallback', '', 10);
	$html = '';
	$html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%">致: &nbsp;</td>
					<td width="35%" rowspan="4"><b>'.$send_to.'</b></td>
					<td width="20%">編號: &nbsp;</td>
					<td width="30%"><b>'.$result1['pcid'].'</b></td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="20%">客戶: &nbsp;</td>
					<td width="30%"><b>'.$result1['customer'].'</b></td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="20%">客人 PO#: &nbsp;</td>
					<td width="30%"><b>'.$result1['customer_po'].'</b></td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="20%" style="background-color:#fffe00">要求出貨日期: &nbsp;</td>
					<td width="30%" style="background-color:#fffe00"><b>'.$expected_date.'</b></td>
				</tr>
				<tr>
					<td width="15%">聯絡人: &nbsp;</td>
					<td width="35%"><b>'.$result1['attention'].'</b></td>
					<td width="20%">日期: &nbsp;</td>
					<td width="30%"><b>'.$make_date.'</b></td>
				</tr>
				<tr>
					<td width="15%">參考: &nbsp;</td>
					<td width="35%"><b>'.$result1['reference'].'</b></td>
					<td width="35%"></td>
					<td width="20%">&nbsp;</td>
				</tr>	
				<tr>
					<td width="15%">備註: &nbsp;</td>
					<td width="35%" rowspan="2"><b>'.$result1['remark'].'</b></td>
					<td width="35%"></td>
					<td width="20%">&nbsp;</td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="35%"></td>
					<td width="20%">&nbsp;</td>
				</tr>															
				</table>';
	$pdf->writeHTML($html, false, false, true, false, '');
	
	$html = '';
	//cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
	$html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<th width="5%">&nbsp;</th>
					<th width="15%" align="left">廠號</th>
					<th width="15%" align="left">規格</th>
					<th width="15%">編號</th>
					<th width="6%" align="right">數量</th>
					<th width="12%" align="right">價格</th>
					<th width="12%" align="right">總和</th>
					<th width="20%">圖片</th>
				</tr></table>';	
	$pdf->writeHTML($html, false, false, true, false, '');
	$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());	
	$html = '';

// 新方法
$total = 0;
//product 的個數
$rtn_num = count($result2);


for($i = 0; $i < count($result2); $i++){
	//為了將description數據庫中存儲的 \r\n 轉為<br />
	$result2[$i]['description_chi'] = str_replace("\r\n", '<br />', $result2[$i]['description_chi']);
	
	$img_html = '';
	if (is_file('../../' . $pic_path_com . $result2[$i]['photos']) == true) { 
		/*
		$arr = getimagesize('../../' . $pic_path_com . $result2[$i]['photos']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(115, 85, $pic_width, $pic_height);
		//$img_html = '<div class="imgdiv"><img src="/sys/'.$pic_path_com . $result2[$i]['photos'].'" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></div>';
		$img_html = '<img src="/sys/'.$pic_path_com . $result2[$i]['photos'].'" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/>';
		*/
		
		//壓縮圖片
		//$result2[$i]['photos']是原來的， $mid_photo 是縮小後的
		//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
		$mid_photo = 'm_' . $result2[$i]['photos'];
		//縮小的圖片不存在才進行縮小操作
		if (!is_file('../../' . $pic_path_small . $mid_photo) == true) { 	
			makethumb('../../' . $pic_path_com . $result2[$i]['photos'], '../../' . $pic_path_small . $mid_photo, 'm');
		}
		//寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用m_的圖片，分辨率高點
		$img_html = '<img src="/sys/'.$pic_path_small . 'm_'.$result2[$i]['photos'].'" align="middle" />';
	}
	$html = '';
	if( $i < 6){
		//CSS必須包含在每一次的 writeHTML 中，否則沒有效，這並不是像普通的html頁，包含在最頂，就整個頁面有效
		$html .= '
		<style>
		.imgdiv {
			background-color: #ffffff;
			border: 1px solid black;
		}
		</style>';
		$html .= '<table align="center" cellpadding="1" cellspacing="1">';
		$html .= '<tr>
					<td width="5%" align="left">'.($i+1).'</td>
					<td width="30%" colspan="2" align="left"><b>'.$result2[$i]['pid'].'</b></td>
					<td width="15%">'.$result2[$i]['ccode'].'</td>
					<td width="6%" align="right">'.intval($result2[$i]['quantity']).'</td>
					<td width="12%" align="right">'.formatMoney($result2[$i]['price']).'</td>
					<td width="12%" align="right"><b>'.formatMoney(intval($result2[$i]['quantity'])*sprintf("%01.2f", round(floatval($result2[$i]['price']),2))).'</b></td>
					<td width="20%" rowspan="2">'.$img_html.'</td>
				</tr>
				<tr>
					<td height="62" width="5%">&nbsp;</td>
					<td width="15%">'.$result2[$i]['scode'].'</td>
					<td colspan="4" align="left">'.$result2[$i]['description_chi'].'</td>
				</tr>';
		$html .= '</table>';	
		//還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
		//$html .= ($i == count($result2)-1)?'':'<hr />';	
		$pdf->writeHTML($html, false, false, true, false, '');
		$pdf->Ln(2);
		if($i != count($result2)-1){
			$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());	
		}		
	}else{
		$html = '';
		//為在新的一頁加表頭
		if( ($i - 6) % 8 == 0 ){
			$j = 0; //标记第二也起每页的product个数，8个满，输出html
			$pdf->AddPage();
			$html .= '<div></div><table align="center" cellpadding="1" cellspacing="1">
					<tr>
					<th width="5%">&nbsp;</th>
					<th width="13%" align="left">廠號</th>
					<th width="18%" align="left">規格</th>
					<th width="10%">客號</th>
					<th width="6%" align="right">數量</th>
					<th width="16%" align="right">價格</th>
					<th width="12%" align="right">總和</th>
					<th width="20%">圖片</th>
				</tr></table>';
			$pdf->SetFont('droidsansfallback', '', 10);
			$pdf->writeHTML($html, false, false, true, false, '');
			$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());	
			$html = '';
		}
		
		$html .= '
		<style>
		.imgdiv {
			background-color: #ffffff;
			border: 1px solid black;
			margin-top:5px;
		}
		</style>';
		$html .= '<table align="center" cellpadding="1" cellspacing="1">
		<tr>
			<td width="5%" align="left">'.($i+1).'</td>
			<td width="29%" colspan="2" align="left"><b>'.$result2[$i]['pid'].'</b></td>
			<td width="16%">'.$result2[$i]['ccode'].'</td>
			<td width="6%"  align="right">'.intval($result2[$i]['quantity']).'</td>
			<td width="12%"  align="right">'.formatMoney($result2[$i]['price']).'</td>
			<td width="12%" align="right"><b>'.formatMoney(intval($result2[$i]['quantity'])*sprintf("%01.2f", round(floatval($result2[$i]['price']),2))).'</b></td>
			<td width="20%" rowspan="2">'.$img_html.'</td>
		</tr>
		<tr>
			<td height="62" width="5%">&nbsp;</td>
			<td width="13%">&nbsp;</td>
			<td colspan="4" align="left">'.$result2[$i]['description_chi'].'</td>
		</tr>
		</table>';
		//還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
		//$html .= ($i == count($result2)-1)?'':'<hr />';
		$pdf->SetFont('droidsansfallback', '', 10);
		$pdf->writeHTML($html, false, false, true, false, '');	
		$pdf->Ln(2);
		if($i != count($result2)-1){
			$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());	
		}		
		$html = '';
	}
	$total += intval($result2[$i]['quantity'])*sprintf("%01.2f", round(floatval($result2[$i]['price']),2));
}
$total = formatMoney($total);

/* 原來的做法，導致只有一頁，但也有可能是我不知道怎麼讀取頁數
$total = 0;
$i = 1;
//當前的page，從1開始
$page_now = 1;
foreach($result2 as $v){
	$img_html = '';
	if (is_file('../../' . $pic_path_com . $v['photos']) == true) { 
		$arr = getimagesize('../../' . $pic_path_com . $v['photos']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(160, 120, $pic_width, $pic_height);
		$img_html = '<img src="/sys/'.$pic_path_com . $v['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/>';
	}
	if($pdf->getPage() > $page_now){
		$page_now = $pdf->getPage();
		$html .= '<tr>
					<td width="5%">&nbsp;</td>
					<td width="13%">ITEM</td>
					<td width="20%">DESC</td>
					<td width="10%">CAT NO.</td>
					<td width="6%">QTY</td>
					<td width="16%">NET PRICE</td>
					<td width="10%">AMOUNT</td>
					<td width="20%">PHOTO</td>
				</tr>
				<hr />';
	}
	
	$html .= '<tr>
				<td height="110">'.$i.'</td>
				<td><b>'.$v['pid'].'</b></td>
				<td>'.$v['description'].'</td>
				<td>&nbsp;</td>
				<td><b>'.intval($v['quantity']).'</b></td>
				<td><b>'.floatval($v['price']).'</b></td>
				<td><b>'.(intval($v['quantity'])*floatval($v['price'])).'</b></td>
				<td>'.$img_html.'</td>
				</tr><hr />';	//這裡加了<tr><td>&nbsp;</td></tr>這個居然會每一頁最後一張圖片，一定會移到下一頁。。。
				
	$total += (intval($v['quantity'])*floatval($v['price']));
	$i++;	
}
*/
	
	$html = '';
	$html .= '<table cellpadding="1" cellspacing="1"><hr />
				<tr>
				<td width="5%">&nbsp;</td>
				<td width="13%">&nbsp;</td>
				<td width="16%">&nbsp;</td>
				<td width="16%">&nbsp;</td>
				<td width="6%">&nbsp;</td>
				<td align="right" width="12%">總和:</td>
				<td width="12%" align="right"><b>'.$total.'</b></td>
				<td width="20%">&nbsp;</td>
				</tr>
				<tr><td>&nbsp;</td></tr>';
	/*			
	if($result1['discount'] != '' && $result1['discount'] != 0){				
		$html .= '<tr>
					<td colspan="6" align="right"><b>DISCOUNT: </b></td>
					<td width="10%" align="right"><b>'.formatMoney($result1['discount']).'</b></td>
					<td width="20%">&nbsp;</td>				
				</tr>';
	}else{
		$html .= '<tr><td>&nbsp;</td></tr>';	
	}
	*/
	$html .= '<hr><tr>
				<td colspan="6" align="right">出廠價總和 (RMB):</td>
				<td width="12%" align="right"><b>'.$total/*mySub($total, $result1['discount'])*/.'</b></td>
				<td width="20%">&nbsp;</td>
				</tr>';
		
	$html .= '<tr><td>&nbsp;</td></tr>';
				
	$html .= '</table>';
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
	$packaging = str_replace("\r\n", '<br />', $result1['packaging']);
	//注意這裡是替換連著的兩個空格，如果替換一個空格，連單詞之間的空格也替換了
	$ship_mark = str_replace("\r\n", '<br />', str_replace('  ','&nbsp;&nbsp;',$result1['ship_mark']));
	//為了將remarks數據庫中存儲的 \r\n 轉為<br />
	$remarks = str_replace("\r\n", '<br />', $result1['remarks']);
	//不知什麼原因textarea的所有文字前面的回車會被省略沒有保存如數據庫，所以這裡判斷文字中如果有回車，就在最前面加了一個
	//if(strpos($result1['remarks'], "\r\n")){
		//$remarks = '<br />' . $remarks;
	//}
	//$html .= '<div align="left">SAY EX-FACTORY: (RMB) &nbsp;'.umoney($total/*mySub($total, $result1['discount'])*/).'</div><br /><br /><br />';
	$html .= '<span style="text-decoration:underline"><font size="+2">備註:&nbsp;&nbsp;</font></span><br /><br />1、包裝: <br />'.$packaging.'<br /><br />';	
	$html .= '2、船嘜: <br />'.$ship_mark.'<br /><br />';	
	$html .= '3、其他: <br />'.$remarks.'<br /><br /><br /><br /><br />';
	/*
	$html .= '<table width="100%" style= "border:1px solid black" cellpadding="3" cellspacing="3">
				<tr align="left">
					<td width="50%"><font size="+2">審核(樂思):</font></td>
					<td width="50%"><font size="+2">確認(工廠):</font></td>
				</tr>
				<br />
				<br />
				<br />
				<br />
				<tr valign="baseline">
					<td><hr width="60%" valign="baseline"></td>
					<td><hr width="60%"></td>
				</tr>
				<tr>
					<td>日期:</td>
					<td>日期:</td>					
				</tr>
			</table>';
	*/
	$html .= '<table width="100%"><tr><td><img src="/sys/images/sign.jpg" /></td></tr></table>';

	// output the HTML content
	$pdf->writeHTML($html, false, false, true, false, '');
	
	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	
	//Close and output PDF document
	$pdf->Output($result1['pcid'].'.pdf', 'I');
	
	//============================================================+
	// END OF FILE                                                
	//============================================================+
	

}else{
	die('Error(3)');	
}
?>
