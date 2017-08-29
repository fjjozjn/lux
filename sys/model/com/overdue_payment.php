<?

//禁止其他用户进入（临时做法）
/*
if($_SESSION['logininfo']['aName'] != 'zjn' && $_SESSION['logininfo']['aName'] != 'KEVIN'){
	$myerror->error('Without Permission To Access', 'main');
}
*/

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
if (count($_POST)){
    $_SESSION['search_criteria'] = $_POST;
}elseif(isset($_GET['status'])){//不加这个会有bug，当搜索条件的session已经存在后，在点菜单的尽量，get里的status参数使选择框选中get里的值，但是搜索的结果还是session的
    $_SESSION['search_criteria']['status'] = $_GET['status'];
}

$form = new My_Forms();
$formItems = array(
    /* mod 20120827 让用户不能搜索（虽然本来也就搜不到别人开的单）
    'user' => array(
        'type' => 'text',
        'value' => @$_SESSION['search_criteria']['user'],
        ),
    */
    'status' => array(
        'type' => 'select',
        'options' => $pi_status_overdue_payment,
        'value' => @$_SESSION['search_criteria']['status'],
    ),
    'start_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        //'required' => 1,
        //'nostar' => true,
        'value' => @$_SESSION['search_criteria']['start_date'],
    ),
    'end_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        //'required' => 1,
        //'nostar' => true,
        'value' => @$_SESSION['search_criteria']['end_date'],
    ),
    'payment_start_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['payment_start_date'],
    ),
    'payment_end_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['payment_end_date'],
    ),
    'est_pay_date_start' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['est_pay_date_start'],
    ),
    'est_pay_date_end' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['est_pay_date_end'],
    ),
    'submitbutton' => array(
        'type' => 'submit',
        'value' => 'Search',
        'title' => ''),
);
//mod 20130218 管理员可以选择用户来搜索，其他用户在此处连搜索框都不显示
if(isSysAdmin()){
    $formItems['user'] = array('type' => 'select', 'options' => get_user('sys'), 'value' => @$_SESSION['search_criteria']['user']);
}

$form->init($formItems);
$form->begin();

?>

    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">OVERDUE PAYMENT</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td height="35" align="right">User : </td>
                            <td align="left">
                                <?
                                if(!isSysAdmin()){
                                    echo $_SESSION['logininfo']['aName'];
                                }else{
                                    $form->show('user');
                                }
                                ?></td>
                            <td align="right">Status : </td>
                            <td align="left"><? $form->show('status'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Created Date Start : <!--h6 class="required">*</h6--></td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right">Created Date End : <!--h6 class="required">*</h6--></td>
                            <td align="left"><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">PAY DATE Start : </td>
                            <td align="left"><? $form->show('payment_start_date'); ?></td>
                            <td align="right">PAY DATE End : </td>
                            <td align="left"><? $form->show('payment_end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">EST.PAY Start : </td>
                            <td align="left"><? $form->show('est_pay_date_start'); ?></td>
                            <td align="right">EST.PAY End : </td>
                            <td align="left"><? $form->show('est_pay_date_end'); ?></td>
                        </tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <?
                                $form->show('submitbutton');
                                // $form->show('resetbutton');

                                ?><!--div style="padding-top: 9px;">&nbsp;&nbsp;<a class="button" href="model/com/overdue_shipment_pdf.php" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>&nbsp;&nbsp;<a class="button" href="model/com/overdue_shipment_excel.php" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a></div-->
                            </td>
                        </tr>
                        <!--            <tr><td colspan="4" style="color:#F00" align="center">#请填写查询的日期范围，至少填一组。</td></tr>-->
                    </table>
                </fieldset>
            </td>
        </tr>
    </table><br />
<?
$form->end();

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr valign='top'>
    <td align="center" width="80%">

        <?
        //20121213 数据也不多，默认搜索所有
        //20130722 又把条件加上了，太慢了
        //20141026 把必须选时间的条件去掉了
        //	if ((strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])) || (strlen(@$_SESSION['search_criteria']['payment_start_date']) && strlen(@$_SESSION['search_criteria']['payment_end_date'])) || (strlen(@$_SESSION['search_criteria']['est_pay_date_start']) && strlen(@$_SESSION['search_criteria']['est_pay_date_end']))){
        //$sql = 'select pvid, mark_date, printed_by, send_to, reference, istatus from proforma where mark_date between ? and ?';
        $temp_table = ' proforma f';

        //20141026 始终不显示状态为Delete和空的
        $where_sql = " AND f.expected_date <= now() AND f.istatus <> 'Delete' AND f.istatus <> ''";
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND f.mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND f.mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND f.mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['user'])){
            $where_sql.= " AND f.printed_by Like '%".$_SESSION['search_criteria']['user']."%'";
        }
        if (strlen(@$_SESSION['search_criteria']['status'])){
            $status = explode('|', $_SESSION['search_criteria']['status']);
            $where_sql.= " AND (f.istatus Like '%".$status[0]."%' OR f.istatus Like '%".$status[1]."%')";
        }elseif(isset($_GET['status'])){
            $where_sql.= " AND f.istatus Like '%(I)%'";
        }

        if (strlen(@$_SESSION['search_criteria']['payment_start_date'])){
            $temp_table .= ' ,payment p';
            if (strlen(@$_SESSION['search_criteria']['payment_end_date'])){
                $where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.value_date between '".$_SESSION['search_criteria']['payment_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['payment_end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.value_date > '".$_SESSION['search_criteria']['payment_start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['payment_end_date'])){
            $where_sql.= " AND f.pvid = p.pi_no AND p.p_status = 'Balance' AND p.value_date < '".$_SESSION['search_criteria']['payment_end_date']." 23:59:59'";
        }

        //普通用户只能搜索到自己开的单
        if (!isSysAdmin()){
            $where_sql .= " AND printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\'))';
        }

        $where_sql.= ' ORDER BY mark_date';

        $list_field = ' f.pvid, f.mark_date, f.printed_by, f.cid, f.reference, f.istatus, f.expected_date ';
        $start_row = 0;
        //默认值100000，相当于一页显示无限多条的记录了
        $end_row = 100000;

        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$rs = $mysql->q($sql, $_SESSION['search_criteria']['start_date']." 00:00:00", $_SESSION['search_criteria']['end_date']." 23:59:59", (strlen(@$_SESSION['search_criteria']['user']))?'%'.$_SESSION['search_criteria']['user'].'%':'%'.$_SESSION['search_criteria']['status'].'%', '%'.$_SESSION['search_criteria']['status'].'%');
        if($info){
        //$rtn = $mysql->fetch();
        $rtn = $mysql->fetch(0, 1);

        //EST.PAY DATE
        if (strlen(@$_SESSION['search_criteria']['est_pay_date_start']) || strlen(@$_SESSION['search_criteria']['est_pay_date_end'])){
            $result = array();
            foreach($rtn as $v){
                $temp = $mysql->qone('select deposit from customer where cid = ?', $v['cid']);
                $deposit = ($temp['deposit']?$temp['deposit']:0);
                $rtn_shipment = $mysql->qone('select s_date from shipment where pi_no = ?', $v['pvid']);

                $est_pay_date = date('Y-m-d H:i:s', strtotime($rtn_shipment['s_date'])+60*60*24*$deposit);

                if (strlen(@$_SESSION['search_criteria']['est_pay_date_start'])){
                    if (strlen(@$_SESSION['search_criteria']['est_pay_date_end'])){
                        if(strtotime($_SESSION['search_criteria']['est_pay_date_start']) < $est_pay_date && $est_pay_date
                            < strtotime($_SESSION['search_criteria']['est_pay_date_end'])){
                            $result[] = $v;
                        }
                    }else{
                        if(strtotime($_SESSION['search_criteria']['est_pay_date_start']) < $est_pay_date){
                            $result[] = $v;
                        }
                    }
                }elseif (strlen(@$_SESSION['search_criteria']['est_pay_date_end'])){
                    if($est_pay_date < strtotime($_SESSION['search_criteria']['est_pay_date_end'])){
                        $result[] = $v;
                    }
                }
            }
            $rtn = $result;
        }else{
            $result = array();
            foreach($rtn as $v){
                $temp = $mysql->qone('select deposit from customer where cid = ?', $v['cid']);
                $deposit = ($temp['deposit']?$temp['deposit']:0);
                $rtn_shipment = $mysql->qone('select s_date from shipment where pi_no = ?', $v['pvid']);

                $est_pay_date = date('Y-m-d H:i:s', strtotime($rtn_shipment['s_date'])+60*60*24*$deposit);

                if($est_pay_date < dateMore()){
                    $result[] = $v;
                }
            }
            $rtn = $result;
        }

        ?>
        <fieldset>
            <legend class='legend'>Information ( total : <?=$info?> )</legend>
            <table id="mysorter" class="tablesorter" width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
                <!--tr bgcolor='#EEEEEE'>
                    <td>&nbsp;</td>
                    <td colspan="8" align="center">SALES</td>
                    <td colspan="3"></td>
                </tr-->
                <thead>
                <tr bgcolor='#EEEEEE'>
                    <th></th>
                    <th>PO REC DATE</th>
                    <th>STAFF</th>
                    <th>C'ID</th>
                    <th>CUST' PO#</th>
                    <th>PI #</th>
                    <th>TOTAL PI AMOUNT(USD)</th>
                    <th>INVOICE #</th>
                    <th>TOTAL ORDER AMOUNT(USD)</th>
                    <th>SHIP DATE</th>
                    <th>ETD</th>
                    <th width="6%">#of days</th>
                </tr>
                </thead>
                <tbody>
                <?
                $total_pi = 0;
                $total_oa = 0;
                $now = time();
                foreach($rtn as $v){
                    echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";

                    $customer = $v['cid'];

                    //sales
                    //proforma 的总和
                    $proforma_total = 0;
                    //invoice 的总和
                    $sales_total = 0;

                    $invoice_all = '';

                    //proforma
                    $rs_proforma = $mysql->q('select pvid from proforma where pvid like ?', '%'.$v['pvid'].'%');
                    if($rs_proforma){
                        $rtn_proforma = $mysql->fetch();
                        foreach($rtn_proforma as $w){
                            $rs_proforma_item = $mysql->q('select price, quantity from proforma_item where pvid = ?', $w['pvid']);
                            if($rs_proforma_item){
                                $rtn_proforma_item = $mysql->fetch();
                                foreach($rtn_proforma_item as $x){
                                    $proforma_total += $x['price'] * $x['quantity'];
                                }
                            }
                        }
                    }
                    $total_pi += $proforma_total;

                    //invoice
                    $rs_invoice = $mysql->q('select vid from invoice where istatus <> ? and vid like ?', 'delete', '%'.$v['pvid'].'%');
                    if($rs_invoice){
                        $rtn_invoice = $mysql->fetch();
                        foreach($rtn_invoice as $w){
                            $invoice_all .= "<a href='?act=com-modifyinvoice&modid=".$w['vid']."'>".$w['vid']."</a><br />";
                            $rs_invoice_item = $mysql->q('select price, quantity from invoice_item where vid = ?', $w['vid']);
                            if($rs_invoice_item){
                                $rtn_invoice_item = $mysql->fetch();
                                foreach($rtn_invoice_item as $x){
                                    $sales_total += $x['price'] * $x['quantity'];
                                }
                            }
                        }
                    }
                    $total_oa += $sales_total;

                    $rtn_shipment_date = $mysql->qone('select s_date from shipment where pi_no like ? and s_status = ? order by s_date desc', '%'.$v['pvid'].'%', 'Complete');

                    //20150112 以shipment date计算of date
                    $of_day = '';
                    if($rtn_shipment_date['s_date'] != ''){
                        //if($v['istatus'] == '(S)' || $v['istatus'] == '(C)'){
                            $balance = 0;
                            $rtn_customer = $mysql->qone('select balance from customer where cid = ?', $customer);
                            if($rtn_customer['balance'] != ''){
                                $balance = $rtn_customer['balance'];
                            }

                            $s_time = strtotime($rtn_shipment_date['s_date']);

                            /*if($s_time - $balance * 24 * 60 * 60 > strtotime($rtn_shipment_date['s_date']) ){
                                $of_day = datediff('day', strtotime($rtn_shipment_date['s_date']), $s_time - $balance * 24 * 60 * 60);
                            }*/

                            //20150113
                            if($s_time + $balance * 24 * 60 * 60 < $now ){
                                $of_day = datediff('day', $s_time + $balance * 24 * 60 * 60, $now);
                            }

                        /*}elseif($v['istatus'] == '(I)' || $v['istatus'] == '(P)'){

                            $e_time = strtotime($rtn_shipment_date['s_date']);
                            if($e_time < $now){
                                $of_day = datediff('day', $e_time, $now);
                            }
                        }*/
                    }

                    echo "	<td>".$v['istatus']."</td>";
                    echo "	<td>".(($v['mark_date'] == '') ? '' : date('Y-m-d', strtotime($v['mark_date'])))."</td>";
                    echo "	<td>".$v['printed_by']."</td>";
                    echo "	<td>".$customer."</td>";
                    echo "	<td>".$v['reference']."</td>";
                    echo "	<td><a href='?act=com-modifyproforma&modid=".$v['pvid']."'>".$v['pvid']."</a></td>";
                    echo "  <td>".formatMoney($proforma_total)."</td>";
                    echo "	<td align='left'>".$invoice_all."</td>";
                    echo "	<td>".formatMoney($sales_total)."</td>";
                    echo "	<td>".(($rtn_shipment_date['s_date'] == '') ? '' : date('Y-m-d', strtotime($rtn_shipment_date['s_date'])))."</td>";
                    echo "	<td>".(($v['expected_date'] == '') ? '' : date('Y-m-d', strtotime($v['expected_date'])))."</td>";
                    echo "	<td>".$of_day."</td>";

                    echo "</tr>";
                }
                echo "</tbody>";
                echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'><td colspan='6' align='right'>Total：</td><td>".formatMoney($total_pi)."</td><td align='right'>Total：</td><td>".formatMoney($total_oa)."</td><td></td><td></td><td></td></tr>";
                }
                }/*else{
		echo "<script>alert('请填写查询的日期范围，至少填一组。')</script>";	
	}*/
                ?>
            </table>
        </fieldset>
    </td>
</tr>
</table>
<?
//}
?>

<script>
    $(function(){
        $("#mysorter").tablesorter({
            //初始按 #of days 的降序排
            sortList: [ [11,1] ],
            //让前面的10列都不能排
            headers: {
                0: { sorter: false },
                2: { sorter: false },
                3: { sorter: false },
                4: { sorter: false },
                5: { sorter: false },
                6: { sorter: false },
                7: { sorter: false },
                8: { sorter: false },
                9: { sorter: false }
            }
        });
    })
</script>