<?php

/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since 2011.9
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['ftylogininfo'])){
    die('Please login!');
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');
require_once('../../tcpdf/config/lang/eng.php');
require_once('../../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        /*$image_file = K_PATH_IMAGES.'header.jpg';
        $this->Image($image_file, '', '', 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false,
        false);*/
        global $mysql;
        //fb($_SESSION['ftylogininfo']);
        $rtn_user = $mysql->qone('select FtyName from tw_admin where AdminName = (select created_by from fty_sub_contractor_order where id = ?)', $_GET['id']);
        $rtn = $mysql->qone('select sid, name, name_en, address, tel from supplier where sid = ?', $rtn_user['FtyName']);
        $this->SetFont('droidsansfallback', '', 20);
        $this->MultiCell(55, 10, $rtn['name']?$rtn['name']:'Lux 内部使用', 0, 'L', 0, 0, 15, 5, true);
        $this->SetFont('droidsansfallback', '', 10);
        $this->MultiCell(55, 10, $rtn['name_en']?$rtn['name_en']:'', 0, 'L', 0, 0, 15, 15, true);
        //20150616 address与tel移到supplier表
        //$rtn_contact = $mysql->qone('select address, tel1 from contact where sid = ?', $rtn['sid']);
        if($rtn){
            $this->MultiCell(100, 20, '地址：'.trim($rtn['address'])."\r\n".'电话：'.trim($rtn['tel']), 0, 'R', 0, 0, 95, 7, true);
        }
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('droidsansfallback', 'I', 10);
        // Page number
        $this->Cell(200, 10, '第 '.$this->getAliasNumPage().' 页，共 '.$this->getAliasNbPages().' 页', 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

if(isset($_GET['id']) && $_GET['id'] != ''){
    //判断是否有访问权限
    //isFtyAdmin 放在fty/in38/admin_function.php 里，这里用的是sys的函数集，所以再用fty的会有函数重名错误
    /*if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
        $rtn = $mysql->qone("select sid from purchase where pcid = ?", $_GET['id']);
        if($rtn['sid'] != $_SESSION['ftylogininfo']['aName']){
            die('Without Permission To Access');
        }
    }*/

    $result1 = $mysql->qone('select * from fty_sub_contractor_order where id = ?', $_GET['id']);
    if(!$result1){
        die('Error(1)');
    }
    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $send_to = str_replace("\r\n", '<br />', $result1['send_to']);
    $make_date = date('Y/m/d', strtotime($result1['in_date']));
    $expected_date = date('Y/m/d', strtotime($result1['expected_date']));
    $rs2 = $mysql->q('select * from fty_sub_contractor_order_item where main_id = ?', $_GET['id']);
    if($rs2){
        $result2 = $mysql->fetch();

        for($i = 0; $i < count($result2); $i++){
            $temp = substr($result2[$i]['task'], 5);
            $temp_arr = explode('|', $temp);
            $result2[$i]['task'] = '';
            foreach($temp_arr as $t){
                $temp_task_name = $mysql->qone('select t_name from bom_task where t_id = ? and bom_id = (select id from bom where g_id = ?)', $t, $result2[$i]['pid']);
                if($t){
                    $result2[$i]['task'] .= ($t.':'.$temp_task_name['t_name'].', ');
                }
            }
            $result2[$i]['task'] = (($result2[$i]['task'] == '')?'无':trim($result2[$i]['task'], ', '));
        }
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
    $html = '<span align="right"><b>加工單</b><span/>';
    //畫線用了新的GetY方法，就能動態畫線了，此句已經移到下面了
    //$pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());//為了解決<hr />太高不能控制的的bug，用這個來畫表頭下面的直線了
    $pdf->writeHTML($html, true, false, true, false, '');
    //$pdf->Ln(1);

    $pdf->SetFont('droidsansfallback', '', 10);
    $html = '';
    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%">致: &nbsp;</td>
					<td width="35%" rowspan="4"><b>'.$send_to.'</b></td>
					<td width="20%">編號: &nbsp;</td>
					<td width="30%"><b>'.$result1['sco_id'].'</b></td>
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
					<td width="20%">負責人: &nbsp;</td>
					<td width="30%"><b>'.$result1['created_by'].'</b></td>
				</tr>	
				<tr>
					<td width="15%">備註: &nbsp;</td>
					<td width="35%" rowspan="2"><b>'.$result1['remark'].'</b></td>
					<td width="20%">&nbsp;</td>
					<td width="30%">&nbsp;</td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="35%"></td>
					<td width="20%"></td>
					<td width="30%">&nbsp;</td>
				</tr>															
				</table>';
    $pdf->writeHTML($html, false, false, true, false, '');

    $html = '';
    //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
    $html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<th width="5%">&nbsp;</th>
					<th width="13%" align="left">廠號</th>
					<th width="16%" align="left">規格</th>
					<th width="16%">客號</th>
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
        if (is_file('../../sys/' . $pic_path_com . $result2[$i]['photos']) == true) {
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
            if (!is_file('../../sys/' . $pic_path_small . $mid_photo) == true) {
                makethumb('../../sys/' . $pic_path_com . $result2[$i]['photos'], '../../sys/' . $pic_path_small . $mid_photo, 'm');
            }
            //寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用m_的圖片，分辨率高點
            $img_html = '<img src="/sys/'.$pic_path_small . 'm_'.$result2[$i]['photos'].'" align="middle" />';
        }
        $html = '';
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
				<td width="29%" colspan="2" align="left"><b>'.$result2[$i]['pid'].'</b></td>
				<td width="16%">'.$result2[$i]['ccode'].'</td>
				<td width="6%" align="right">'.intval($result2[$i]['quantity']).'</td>
				<td width="12%" align="right">'.$result2[$i]['price'].'</td>
				<td width="12%" align="right"><b>'.sprintf("%01.3f", intval($result2[$i]['quantity'])*floatval($result2[$i]['price'])).'</b></td>
				<td width="20%" rowspan="2">'.$img_html.'</td>
			</tr>
			<tr>
				<td height="62" width="5%">&nbsp;</td>
				<td width="13%">&nbsp;</td>
				<td colspan="4" align="left">'.$result2[$i]['description_chi'].'</td>
			</tr>
			<tr>
				<td colspan="7" align="left">工序 ： '.$result2[$i]['task'].'</td>
			</tr>';
        $html .= '</table>';
        //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
        //$html .= ($i == count($result2)-1)?'':'<hr />';
        $pdf->writeHTML($html, false, false, true, false, '');
        //test test
        //$pdf->writeHTMLCell('',100,$pdf->GetX(),$pdf->GetY(),$html, 1, 0, 0, true, 'J', true);
        $pdf->Ln(2);
        if($i != count($result2)-1){
            $pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());
        }
        //$pdf->writeHTMLCell('','','','',$html);

        $total += intval($result2[$i]['quantity'])*floatval($result2[$i]['price']);
    }
    $total = sprintf("%01.3f", $total);

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

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output($result1['sco_id'].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+


}else{
    die('Error(3)');
}
?>
