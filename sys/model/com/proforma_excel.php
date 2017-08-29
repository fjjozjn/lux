<?php

/**
 * php create pdf
 * @package
 * @abstract
 * @author zjn
 * @since 2011.11
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
//以防未登入就直接访问 
if (!isset($_SESSION['logininfo'])) {
    die();
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if (isset($_GET['pvid']) && $_GET['pvid'] != '') {
    //判断是否有访问权限
    if (!isSysAdmin()) {
        $rtn = $mysql->qone("select printed_by from proforma where pvid = ?", $_GET['pvid']);
        if ($rtn['printed_by'] != $_SESSION['logininfo']['aName']) {
            if (!judgeUserPermGroup($rtn['printed_by'])) {
                die('Without Permission To Access');
            }
        }
    }

    //修改printed_date
    $mysql->q('update proforma set printed_date = ? where pvid = ?', dateMore(), $_GET['pvid']);

    $result1 = $mysql->qone('select * from proforma where pvid = ?', $_GET['pvid']);
    if (!$result1) {
        die('Error(1)');
    }

    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $send_to = str_replace("\r\n", '<br />', $result1['send_to']);
    $make_date = date('Y/m/d', strtotime($result1['mark_date']));
    $printed_date = date('Y/m/d', strtotime($result1['printed_date']));
    $rs2 = $mysql->q('select * from proforma_item where pvid = ?', $_GET['pvid']);
    if ($rs2) {
        $result2 = $mysql->fetch();
    } else {
        die('Error(2)');
    }

    //獲取總頁數，也是通過product個數來計算，暫時不知道怎麼通過tcpdf函數獲取
    if (isset($result2)) {
        $page_nums = (count($result2) <= 6) ? 1 : (intval((count($result2) - 6) / 8) + 2);
    }
    $html = '<h1 align="center">Proforma Invoice</h1>';

    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%" rowspan="4" valign="top">TO: &nbsp;</td>
					<td width="43%" rowspan="4"><b>' . $send_to . '</b></td>
					<td width="20%">INVOICE NO.: &nbsp;</td>
					<td width="22%"><b>' . $result1['pvid'] . '</b></td>
				</tr>
				<tr>
					<td width="20%">DATE: &nbsp;</td>
					<td width="22%"><b>' . $make_date . '</b></td>
				</tr>
				<tr>
					<td width="20%">REFERENCE NO.: &nbsp;</td>
					<td width="22%"><b>' . $result1['reference_num'] . '</b></td>
				</tr>
				<tr>
					<td width="20%">PACKING NO.: &nbsp;</td>
					<td width="22%"><b>' . $result1['packing_num'] . '</b></td>
				</tr>		
				<tr>
					<td width="15%">ATTENTION: &nbsp;</td>
					<td width="43%"><b>' . $result1['attention'] . '</b></td>
					<td width="20%">CURRENCY: &nbsp;</td>
					<td width="22%"><b>' . $result1['currency'] . '</b></td>
				</tr>	
				<tr>
					<td width="15%">TEL: &nbsp;</td>
					<td width="43%"><b>' . $result1['tel'] . '</b></td>
					<td width="20%">UNIT: &nbsp;</td>
					<td width="22"><b>' . $result1['unit'] . '</b></td>
				</tr>
				<tr>
					<td width="15%">FAX: &nbsp;</td>
					<td width="43%"><b>' . $result1['fax'] . '</b></td>
					<td width="20%">PAGE: &nbsp;</td>
					<td width="22%"><b>' . $page_nums . '</b></td>
				</tr>
				<tr>
					<td width="15%">REFERENCE: &nbsp;</td>
					<td width="43%"><b>' . $result1['reference'] . '</b></td>
					<td width="20%">PRINTED BY: &nbsp;</td>
					<td width="22%"><b>' . $_SESSION["logininfo"]["aName"] . '</b></td>
				</tr>
				<tr>
					<td width="15%" rowspan="2">REMARK: &nbsp;</td>
					<td width="43%" rowspan="2"><b>' . $result1['remark'] . '</b></td>
					<td width="20%">PRINTED DATE: &nbsp;</td>
					<td width="22%"><b>' . $printed_date . '</b></td>
				</tr>																																
				</table><div></div>';

    //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
    $html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<th width="5%">&nbsp;</th>
					<th width="10%" align="left">REF</th>
					<th width="12%" align="left">ITEM</th>
					<th width="29%" align="left">DESC</th>
					<th width="10%">CAT NO.</th>
					<th width="6%" align="right">QTY</th>
					<th width="16%" align="right">NET PRICE</th>
					<th width="12%" align="right">AMOUNT</th>
				</tr></table>';

// 新方法
    $total = 0;
//product 的個數
    $rtn_num = count($result2);


    for ($i = 0; $i < count($result2); $i++) {
        //為了將description數據庫中存儲的 \r\n 轉為<br />
        $result2[$i]['description'] = str_replace("\r\n", '<br />', $result2[$i]['description']);

        $img_html = '';
        if (isset($_GET['photo'])) {
            if (is_file('../../' . $pic_path_com . $result2[$i]['photos']) == true) {
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
                $mid_photo = 'm_' . $result2[$i]['photos'];
                //縮小的圖片不存在才進行縮小操作
                if (!is_file('../../' . $pic_path_small . $mid_photo) == true) {
                    makethumb('../../' . $pic_path_com . $result2[$i]['photos'], '../../' . $pic_path_small . $mid_photo, 'm');
                }
                //寬高下面表格中都定好了，img裏就沒必要再做設定了，這裡用m_的圖片，分辨率高點
                $img_html = '<img src="/sys/' . $pic_path_small . 'm_' . $result2[$i]['photos'] . '" align="middle" />';
            }
        }

        $html .= '<table align="center" cellpadding="1" cellspacing="1">';
        $html .= '<tr>
				<td width="5%" align="left">' . ($i + 1) . '</td>
				<td width="10%">&nbsp;</td>
				<td width="41%" colspan="2" align="left"><b>' . $result2[$i]['pid'] . '</b></td>
				<td width="10%">000-000</td>
				<td width="6%" align="right">' . intval($result2[$i]['quantity']) . '</td>
				<td width="16%" align="right">' . formatMoney($result2[$i]['price']) . '</td>
				<td width="12%" align="right"><b>' . formatMoney(intval($result2[$i]['quantity']) * sprintf("%01.2f", round(floatval($result2[$i]['price']), 2))) . '</b></td>
			</tr>
			<tr>
				<td width="5%" height="62">&nbsp;</td>
				<td width="22%" colspan="2" align="left">' . $result2[$i]['ccode'] . '</td>
				<td width="39%" colspan="2" align="left">' . $result2[$i]['description'] . '</td>
				<td width="6%">&nbsp;</td>
				<td width="16%">&nbsp;</td>
				<td width="12%">' . $img_html . '</td>
			</tr>';
        $html .= '</table>';
        //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
        //$html .= ($i == count($result2)-1)?'':'<hr />';
        if ($i != count($result2) - 1) {

        }

        $total += intval($result2[$i]['quantity']) * sprintf("%01.2f", round(floatval($result2[$i]['price']), 2));
    }
    $total = formatMoney($total);

    $html .= '<table cellpadding="1" cellspacing="1"><hr />
				<tr>
				<td align="right" colspan="7"><b>TOTAL: </b></td>
				<td align="right"><b>' . $total . '</b></td>
				</tr>';

    if ($result1['discount'] != '' && $result1['discount'] != 0) {
        $html .= '<tr>
					<td colspan="7" align="right"><b>DISCOUNT: </b></td>
					<td align="right"><b>' . formatMoney($result1['discount']) . '</b></td>
				</tr>';
    } else {
        $html .= '<tr><td>&nbsp;</td></tr>';
    }

    $html .= '<hr><tr>
				<td colspan="7" align="right"><b>EX-FACTORY TOTAL (' . $result1['currency'] . '): </b></td>
				<td align="right"><b>' . formatMoney(mySub($total, $result1['discount'])) . '</b></td>
				</tr>';

    $html .= '<tr><td>&nbsp;</td></tr>';

    $html .= '</table>';

    /*
    例子
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
    $html .= '<div align="left">SAY EX-FACTORY: <b>(' . $result1['currency'] . ') &nbsp;' . umoney(mySub($total, $result1['discount'])) . '</b></div>';
    $html .= '<div align="left">REMARKS: ' . $remarks . '</div>';

    //============================================================+
    // END OF FILE
    //============================================================+

    //输出excel文件
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: filename=' . $result1['pvid'] . '.xls');
    echo $html;
} else {
    die('Error(3)');
}
