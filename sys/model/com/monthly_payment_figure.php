<?php
/**
 * Author: zhangjn
 * Date: 2017/11/26
 * Time: 15:02
 */
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
        'search_by' => array(
            'type' => 'select',
            'options' => array(array("Value Date", "1")/*, array("ETD", "2")*/),
            'value' => @$_SESSION['search_criteria']['search_by'],
            'required' => 1,
            'nostar'=>1,
        ),
        'submit_button' => array(
            'type' => 'submit',
            'value' => 'Search',
            'title' => ''
        ),
    );
    $form->init($formItems);
    $form->begin();

    ?>

    <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">Monthly Payment Figure</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td height="35">Search by <span style="color: red;">*</span> : </td>
                            <td><? $form->show('search_by'); ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="100%" colspan='4'><? $form->show('submit_button'); ?></td>
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
                if (strlen(@$_SESSION['search_criteria']['search_by'])){
                //$s_name = $mysql->qone('select name from supplier where sid = ?',$_SESSION['search_criteria']['supplier']);
                //$rs = $mysql->q('select total, mark_date from purchase where sid = ? and istatus <> ? order by mark_date', $_SESSION['search_criteria']['supplier'], 'delete');
                $currency = get_currency();
                //fb($currency);
                $rs = '';
                if ($_SESSION['search_criteria']['search_by'] == 1) {
                    $rs = $mysql->q('select pn.currency, pn.value_date, sum(pin.received) as total from payment_new pn left join payment_item_new pin on pn.py_no = pin.py_no where pn.istatus <> ? group by pn.py_no', 'delete');
                }/* elseif ($_SESSION['search_criteria']['search_by'] == 2) {
                    $rs = $mysql->q('select total, expected_date as pi_date from proforma where istatus <> ? and expected_date <> ?', 'delete', '');
                }*/

                if($rs){
                $rtn = $mysql->fetch();
                foreach($rtn as $v){
                    $value_date = explode('-', $v['value_date']);
                    if ($v['currency'] == 'USD') {
                        @$pn_total[$value_date[0]][intval($value_date[1])] += round($v['total'], 2);
                    } else {
                        @$pn_total[$value_date[0]][intval($value_date[1])] += round($v['total'] / $currency[$v['currency']] * $currency['USD'], 2);
                    }
                }
                //fb($pn_total);die();
                ?>
                <fieldset>
                    <legend class='legend'>Information (all counted in Value Date of Payment Advice and status except "delete")</legend>
                    <!--                    <div>--><?//=$s_name['name']?><!--</div>-->
                    <br />
                    <table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
                        <tr bgcolor='#EEEEEE'>
                            <th></th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>May</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Oct</th>
                            <th>Nov</th>
                            <th>Dec</th>
                            <th>Total</th>
                        </tr>
                        <?
                        //20141114 改为显示当前年多一年的信息，因为ETD有的会跨到下年
                        for($i = 2011; $i <= (date('Y')+1); $i++){
                            echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
                            echo "<td>".$i."</td>";
                            $all_total = 0;
                            for($j = 1; $j <= 12; $j++){
                                $all_total += @$pn_total[$i][$j];
                                echo "<td>".formatMoney(@$pn_total[$i][$j])."</td>";
                            }
                            echo "<td>".formatMoney($all_total)."</td>";
                            echo "</tr>";
                        }
                        }else{
                            ?>
                            <fieldset style="width:475px;">
                                <legend class='legend'>Information</legend>
                                <div>没有记录</div>
                            </fieldset>
                        <?
                        }
                        }
                        ?>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
<?
}
?>