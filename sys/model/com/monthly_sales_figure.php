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
        'search_by' => array(
            'type' => 'select',
            'options' => array(array("Creation Date", "1"), array("ETD", "2")),
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

    <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">Monthly Sales Figure</td>
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
                            <td width="100%" colspan='4'><? $form->show('submitbutton'); ?></td>
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
                    $rs = $mysql->q('select total, expected_date from proforma where istatus <> ? and expected_date <> ?', 'delete', '');
                    if($rs){
                        $rtn = $mysql->fetch();
                        $purchase_total = array();
                        foreach($rtn as $v){
                            $the_etd_date = explode('-', $v['expected_date']);
                            @$pi_etd_total[$the_etd_date[0]][intval($the_etd_date[1])] += $v['total'];
                        }
                        //fb($pi_etd_total);die();
                        ?>
                        <fieldset>
                            <legend class='legend'>Information (all counted in ETD of PI and status except "delete")</legend>
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
                                        $all_total += @$pi_etd_total[$i][$j];
                                        echo "<td>".formatMoney(@$pi_etd_total[$i][$j])."</td>";
                                    }
                                    echo "<td>".formatMoney($all_total)."</td>";
                                    echo "</tr>";
                                }
                            }else{
                                ?>
                                <fieldset style="width:475px;">
                                    <legend class='legend'>Information</legend>
                                    <div><?=$s_name['name']?> 没有记录</div>

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
