<?php

/**
 * php create excel
 * @package
 * @abstract
 * @author zjn
 * @since 2011.11
 */

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global_pdf.php');

if(!isset($_SESSION['logininfo'])){
    die();
}

require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

if(isset($_GET['pl_id']) && $_GET['pl_id'] != ''){
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

    //修改printed_date
    $mysql->q('update packing_list set printed_date = ? where pl_id = ?', dateMore(), $_GET['pl_id']);

    $result1 = $mysql->qone('select * from packing_list where pl_id = ?', $_GET['pl_id']);
    if(!$result1){
        die('Error(1)');
    }
    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $ship_to = str_replace("\r\n", '<br />', $result1['ship_to']);
    $in_date = date('Y/m/d', strtotime($result1['in_date']));
    $printed_date = date('Y/m/d', strtotime($result1['printed_date']));
    //20130731 加当同属于一个cart_no的时候后面几项有值的排前面
    $rs2 = $mysql->q('select * from packing_list_item where pl_id = ? order by cart_no asc, gross_weight desc, size_l desc, size_w desc, size_h desc, cbm desc', $_GET['pl_id']);
    if($rs2){
        $result2 = $mysql->fetch();
    }else{
        die('Error(2)');
    }

    $html = '<span align="right"><b>Packing List</b><span/>';

    //20130723 cindy 让把 reference_no 从 REFERENCE NO. 换到 P.O.#
    $html .= '<hr height="2"><table align="left" cellpadding="1" cellspacing="1">
				<tr>
					<td width="15%" rowspan="4">TO: &nbsp;</td>
					<td width="40%" rowspan="4">'.$ship_to.'</td>
					<td width="23%">PACKING LIST NO.: &nbsp;</td>
					<td width="22%">'.$result1['pl_id'].'</td>
				</tr>
				<tr>
					<td width="23%">DATE: &nbsp;</td>
					<td width="22%">'.$in_date.'</td>
				</tr>
				<tr>
					<td width="23%">REFERENCE NO.: &nbsp;</td>
					<td width="22%">&nbsp;</td>
				</tr>
				<tr>
					<td width="23%">UNIT: &nbsp;</td>
					<td width="22%">'.$result1['unit'].'</td>
				</tr>		
				<tr>
					<td width="15%">TEL: &nbsp;</td>
					<td width="40%">'.$result1['tel'].'</td>
					<td width="23%">PAGE: &nbsp;</td>
					<td width="22%">'.$page_nums.'</td>
				</tr>	
				<tr>
					<td width="15%">P.O.#: &nbsp;</td>
					<td width="40%">'.$result1['reference_no'].'</td>
					<td width="23%">PRINTED BY: &nbsp;</td>
					<td width="22%">'.$_SESSION["logininfo"]["aName"].'</td>
				</tr>
				<tr>
					<td width="15%">&nbsp;</td>
					<td width="40%">&nbsp;</td>
					<td width="23%">PRINTED DATE: &nbsp;</td>
					<td width="22%">'.$printed_date.'</td>
				</tr>																															
				</table><div></div>';

    //cellpadding="1" cellspacing="1" 這兩個真是好東西，我的表格內容再也不會擠到一塊了！
    $html .= '<table align="center" cellpadding="1" cellspacing="1">
				<tr>
					<th width="10%">CARTON#</th>
					<th width="16%">CLIENT#</th>
					<th width="16%">ITEM#</th>
					<th width="10%">QTY</th>
					<th width="16%">GROSS WEIGHT (KG)</th>
					<th width="18%">MEASURMENT (CM)</th>
					<th width="14%">CBM</th>
				</tr></table>';

    $html .= '<table align="center" cellpadding="1" cellspacing="1">';
    for($i = 0; $i < count($result2); $i++){

        //20130731 下面三个值为0或空则不显示
        $gross_weight = ($result2[$i]['gross_weight'] == '' || $result2[$i]['gross_weight'] == 0)?'':$result2[$i]['gross_weight']. 'KG';
        $measurment = '';
        $cbm = '';
        if(($result2[$i]['size_l'] == '' || $result2[$i]['size_l'] == 0) &&
            ($result2[$i]['size_w'] == '' || $result2[$i]['size_w'] == 0) &&
            ($result2[$i]['size_h'] == '' || $result2[$i]['size_h'] == 0)){
            $measurment = '';
            $cbm = '';
        }else{
            $measurment = $result2[$i]['size_l'].' * '.$result2[$i]['size_w'].' * '.$result2[$i]['size_h'];
            $cbm = $result2[$i]['size_l']*$result2[$i]['size_w']*$result2[$i]['size_h']/1000000;
        }

        $html .= '<tr>
					<td width="10%">'.$result2[$i]['cart_no'].'</td>
					<td width="16%">'.$result2[$i]['client_no'].'</td>
					<td width="16%">'.$result2[$i]['item'].'</td>
					<td width="10%">'.$result2[$i]['qty'].'</td>
					<td width="16%">'.$gross_weight.'</td>
					<td width="18%">'.$measurment.'</td>
					<td width="14%">'.round($cbm,3).'</td>
				</tr>
				<tr><td>&nbsp;</td></tr>';
    }
    $html .= '</table>';

    $html .= '<table align="center" cellpadding="1" cellspacing="1">';
    $html .= '<tr>
				<td width="42%">TOTAL: '.$result1['total_cart'].' CARTONS</td>
				<td width="10%">'.$result1['total_qty'].' '.$result1['unit'].'</td>
				<td width="16%">'.$result1['total_weight'].' KG</td>
				<td width="18%">&nbsp;</td>
				<td width="14%">'.$result1['total_cbm'].'</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td colspan="7" align="left">COUNTRY OF ORIGIN: CHINA</td>
			</tr>
			<tr>
				<td colspan="7" align="left">REMARK : '.$result1['remark'].'</td>
			</tr>';;
    $html .= '</table>';
    //還是為了<hr />的高度問題。。。writeHTML的html中的第一個hr會有高度，後面的就沒有了。。。
    //$html .= ($i == count($result2)-1)?'':'<hr />';

    //============================================================+
    // END OF FILE
    //============================================================+

    //输出excel文件
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: filename='.$result1['pl_id'].'.xls');
    echo $html;

}else{
    die('Error(3)');
}