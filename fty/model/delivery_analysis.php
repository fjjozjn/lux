<?

//禁止其他用户进入（临时做法）
//20141212 普通用户也可浏览，但只能看自己工厂的
/*if(!isFtyAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}*/

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    //引用特殊的recordset class 文件
    require_once(ROOT_DIR.'fty/in38/recordset.class3.php');

    // 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
    if (count($_POST)){
        $_SESSION['search_criteria'] = $_POST;
    }

    $form = new My_Forms();
    $formItems = array(
        'supplier' => array(
            'type' => 'select',
            'options' => get_supplier_fty(),
            'value' => @$_SESSION['search_criteria']['supplier'],
            'required' => 1,
            'nostar'=>1,
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => '搜索',
            'title' => ''),
    );
    $form->init($formItems);
    $form->begin();

    ?>

    <table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">每月出货统计</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>搜索</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td height="35">工厂名 <span style="color: red;">*</span> : </td>
                            <td><? $form->show('supplier'); ?></td>
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

                    //金额
                    $rs = $mysql->q('select sum(di.price*di.quantity) as total_all, d.d_date from delivery d, delivery_item di where d.d_id = di.d_id and d.sid = ? group by d.d_id order by d.in_date', $_SESSION['search_criteria']['supplier']);

                    if($rs){
                        $rtn = $mysql->fetch();
                        $delivery_total = array();
                        foreach($rtn as $v){
                            $the_in_date = explode('-', $v['d_date']);
                            @$delivery_total[$the_in_date[0]][intval($the_in_date[1])] += $v['total_all'];
                        }
                        ?>
                        <fieldset>
                            <legend class='legend'>结果 ( 出货单按 “出货日期” 统计月出货 “总金额” )</legend>
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
                                        $all_total += @$delivery_total[$i][$j];
                                        echo "<td>".(isset($delivery_total[$i][$j])?formatMoney($delivery_total[$i][$j]):'')."</td>";
                                    }
                                    echo "<td>".formatMoney($all_total)."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </fieldset>
                        <br />


                        <?
                        //数量
                        $rs = $mysql->q('select sum(di.quantity) as total_all, d.d_date from delivery d, delivery_item di where d.d_id = di.d_id and d.sid = ? group by d.in_date order by d.in_date', $_SESSION['search_criteria']['supplier']);
                        $rtn = $mysql->fetch();
                        $delivery_total = array();
                        foreach($rtn as $v){
                            $the_in_date = explode('-', $v['d_date']);
                            @$delivery_total[$the_in_date[0]][intval($the_in_date[1])] += $v['total_all'];
                        }
                        ?>
                        <fieldset>
                            <legend class='legend'>结果 ( 出货单按 "出货日期" 统计月出货 "总数量" )</legend>
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
                                        $all_total += @$delivery_total[$i][$j];
                                        echo "<td>".@$delivery_total[$i][$j]."</td>";
                                    }
                                    echo "<td>".$all_total."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </fieldset>
                    <?
                    }else{
                        ?>
                        <fieldset style="width:475px;">
                            <legend class='legend'>结果</legend>
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
