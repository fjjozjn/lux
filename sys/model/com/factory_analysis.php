<?

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    //引用特殊的recordset class 文件
    require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

    // 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
    if (count($_POST)){
        $_SESSION['search_criteria'] = $_POST;
    }

    $form = new My_Forms();
    $formItems = array(
        'supplier' => array(
            'type' => 'select',
            'options' => $supplier,
            'value' => @$_SESSION['search_criteria']['supplier'],
            'required' => 1,
            'nostar'=>1,
        ),
        'search_by' => array(
            'type' => 'select',
            'options' => array(array("Creation Date", "1"), array("ETD", "2"), array("Actual", "3")),
            'value' => @$_SESSION['search_criteria']['search_by'],
            'required' => 1,
            'nostar'=>1,
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => 'Search',
            'title' => ''),
    );
    $form->init($formItems);
    $form->begin();

    ?>

    <table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">Factory Analysis</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td height="35">Supplier <span style="color: red;">*</span> : </td>
                            <td><? $form->show('supplier'); ?></td>
                        </tr>
                        <tr>
                            <td height="35">Search by <span style="color: red;">*</span> : </td>
                            <td><? $form->show('search_by'); ?></td>
                        </tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <?
                                $form->show('submitbutton');
                                // $form->show('resetbutton');

                                ?></td>
                        </tr>
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
                if (strlen(@$_SESSION['search_criteria']['supplier'])){

                    $s_name = $mysql->qone('select name from supplier where sid = ?', $_SESSION['search_criteria']['supplier']);

                    if($_SESSION['search_criteria']['search_by'] == 1){
                        $rs = $mysql->q('select total, mark_date from purchase where sid = ? and istatus <> ? order by mark_date', $_SESSION['search_criteria']['supplier'], 'delete');
                        if($rs){
                            $rtn = $mysql->fetch();
                            $purchase_total = array();
                            foreach($rtn as $v){
                                $the_mark_date = explode('-', $v['mark_date']);
                                @$purchase_total[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total'];
                            }
                        }

                    }elseif($_SESSION['search_criteria']['search_by'] == 2){
                        $rs = $mysql->q('select total, expected_date as mark_date from purchase where sid = ? and istatus <> ? order by expected_date', $_SESSION['search_criteria']['supplier'], 'delete');
                        if($rs){
                            $rtn = $mysql->fetch();
                            $purchase_total = array();
                            foreach($rtn as $v){
                                $the_mark_date = explode('-', $v['mark_date']);
                                @$purchase_total[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total'];
                            }
                        }

                    }elseif($_SESSION['search_criteria']['search_by'] == 3){//20141212 以Packing List 的總金額來統計 (Packing List 是沒有價格, 要和相關Invoice連接)

                        //按packing list来统计
                        /*
                        $p_rs = $mysql->q('select reference from purchase where sid = ? and istatus <> ? group by reference', $_SESSION['search_criteria']['supplier'], 'delete');
                        if($p_rs){
                            $p_rtn = $mysql->fetch();
                            $invoice = array();
                            foreach($p_rtn as $v){
                                $v_rs = $mysql->q('select vid from invoice where vid like ? and istatus <> ? group by vid', '%'.$v['reference'].'%', 'delete');
                                if($v_rs){
                                    $v_rtn = $mysql->fetch();
                                    foreach($v_rtn as $w){
                                        if(@!in_array($invoice, $w['vid'])){
                                            $invoice[] = $w['vid'];
                                        }
                                    }
                                }
                            }

                            $purchase_total = array();
                            for($i = 2011; $i <= (date('Y')+1); $i++){
                                for($j = 1; $j <= 12; $j++){
                                    $total = 0;
                                    foreach($invoice as $w){
                                        $pli_rs = $mysql->q('select item, qty from packing_list_item where ref = ? and pl_id in (select pl_id from packing_list where DATE_FORMAT(in_date, ?) = ?)', $w, '%Y%m', $i.str_pad($j, 2, '0', STR_PAD_LEFT));

                                        if($pli_rs){
                                            $pli_rtn = $mysql->fetch();
                                            foreach($pli_rtn as $v){
                                                $item = $mysql->qone('select cost_rmb from product where pid = ?', $v['item']);
                                                $total += $item['cost_rmb']*$v['qty'];
                                            }
                                        }
                                    }
                                    $purchase_total[$i][$j] = $total;
                                }
                            }
                        }
                        */


                        //20141222 按 delivery 来统计
                        /*
                        $p_rs = $mysql->q('select pcid from purchase where sid = ? and istatus <> ? order by pcid', $_SESSION['search_criteria']['supplier'], 'delete');
                        if($p_rs){
                            $p_rtn = $mysql->fetch();
                            $purchase_total = array();
                            foreach($p_rtn as $v){
                                $d_rs = $mysql->q('select d.d_date, sum(di.quantity * di.price) as total from delivery d, delivery_item di where d.d_id = di.d_id and di.po_id = ? group by d.d_id', $v['pcid']);
                                if($d_rs){
                                    $d_rtn = $mysql->fetch();
                                    foreach($d_rtn as $v){
                                        $the_mark_date = explode('-', $v['d_date']);
                                        @$purchase_total[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total'];
                                    }
                                }
                            }
                        }
                        */

                        //20150102 与工厂的报表数据一样
                        $d_rs = $mysql->q('select sum(di.price*di.quantity) as total, d.d_date from delivery d, delivery_item di where d.d_id = di.d_id and d.sid = ? group by d.d_id order by d.in_date', $_SESSION['search_criteria']['supplier']);
                        if($d_rs){
                            $d_rtn = $mysql->fetch();
                            foreach($d_rtn as $v){
                                $the_mark_date = explode('-', $v['d_date']);
                                @$purchase_total[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total'];
                            }
                        }

                    }

                    if(isset($purchase_total) && !empty($purchase_total)){
                        ?>
                        <fieldset>
                            <legend class='legend'>
                                <?php
                                if($_SESSION['search_criteria']['search_by'] == 3){
                                    echo 'Information (Monthly Total Amount of Delivery)';
                                }else{
                                    echo 'Information (Monthly Total Amount of Factory PO except "delete" status)';
                                }
                                ?>
                            </legend>
                            <div><?=$s_name['name']?></div>
                            <br />
                            <table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
                                <tr bgcolor='#EEEEEE'>
                                    <th width="7%"></th>
                                    <th width="7%">Jan</th>
                                    <th width="7%">Feb</th>
                                    <th width="7%">Mar</th>
                                    <th width="7%">Apr</th>
                                    <th width="7%">May</th>
                                    <th width="7%">Jun</th>
                                    <th width="7%">Jul</th>
                                    <th width="7%">Aug</th>
                                    <th width="7%">Sep</th>
                                    <th width="7%">Oct</th>
                                    <th width="7%">Nov</th>
                                    <th width="7%">Dec</th>
                                    <th width="9%">Total</th>
                                </tr>
                                <?
                                //20141113 改为显示当前年多一年的信息，因为ETD有的会跨到下年
                                for($i = 2011; $i <= (date('Y')+1); $i++){
                                    echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
                                    echo "<td>".$i."</td>";
                                    $all_total = 0;
                                    for($j = 1; $j <= 12; $j++){
                                        $all_total += @$purchase_total[$i][$j];
                                        echo "<td>".((isset($purchase_total[$i][$j]) && $purchase_total[$i][$j])?formatMoney($purchase_total[$i][$j]):'')."</td>";
                                    }
                                    echo "<td>".formatMoney($all_total)."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </fieldset>
                    <?php
                    }else{
                        ?>
                        <fieldset style="width:475px;">
                            <legend class='legend'>Information</legend>
                            <div><?=$s_name['name']?> 没有记录</div>

                        </fieldset>
                    <?
                    }
                }
                ?>

            </td>
        </tr>
    </table>
<?
}
?>
