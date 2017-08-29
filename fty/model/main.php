<!--<div style="text-align:center; margin:100px auto; color:#06C; font-weight:bold;">欢迎来到 樂思供應鏈管理系統 。</div>-->

<?php

//20130813 只显示po，而且不同于sys的，这里的po一个一行显示

//从导航栏点过带的会带 today 参数，这样默认显示当天的前5后24天，加当天共30天
$chart_date = cal_date_new(date('Y-m-d',strtotime('-5 day')), date('Y-m-d',strtotime('+24 day')));
//fb($chart_date);
//找出在区间内的po
$start_date = $chart_date[0];
$end_date = $chart_date[count($chart_date) -1];
$po = array();
//20130813 加非(D)状态单
if(isFtyAdmin()){
    $rs = $mysql->q('select pcid, in_date, expected_date, istatus from purchase where in_date < ? and expected_date > ? and istatus <> ? and istatus <> ? order by expected_date', $end_date, $start_date, '(D)', 'delete');
}else{
    $rs = $mysql->q('select pcid, in_date, expected_date, istatus from purchase where in_date < ? and expected_date > ? AND sid = ? and istatus <> ? and istatus <> ? order by expected_date', $end_date, $start_date, $_SESSION['ftylogininfo']['aFtyName'], '(D)', 'delete');
}
if($rs){
    $rtn = $mysql->fetch();
    $index = 0;
    foreach($rtn as $v){
        $po[$index]['pcid'] = $v['pcid'];
        $po[$index]['istatus'] = $v['istatus'];
        if($v['istatus'] == '(S)' || $v['istatus'] == '(C)'){
            $rtn = $mysql->qone('select d.d_date from delivery d, delivery_item di where d.d_id = di.d_id and di.po_id = ? order by d.mod_date desc limit 1', $v['pcid']);
            $po[$index]['d_date'] = date('Y-m-d', strtotime($rtn['d_date']));
        }
        $po[$index]['in_date'] = date('Y-m-d', strtotime($v['in_date']));
        $po[$index]['expected_date'] = date('Y-m-d', strtotime($v['expected_date']));
        $index++;
    }
}

//fb($po);

//*******************参数设置*******************
//设置日期和pi po的字体大小
$font_size = 10;
//设置日期表头每个单元格宽度
$td_width = 30;
//设置除顶部表头外每行的高度
$tr_height = 20;
//设置po进度条背景颜色
$po_bg_color = '#66CCFF';//天依蓝
//设置表格只有横线的border
$attr = 'style=" border-collapse:collapse; border:1px solid #DADADA" rules="rows"';
//*******************参数设置*******************

?>
<div align="center" style="width:100%">
    <fieldset class="center2col" style="width:90%; padding-left:5px">
        <legend class='legend'>近期的订单状况</legend>
        <!--<font color="#0066CC"><b><span style="background-color:<?/*=$pi_bg_color*/?>">&nbsp;PI Colour&nbsp;</span> <span style="background-color:<?/*=$po_bg_color*/?>">&nbsp;PO Colour&nbsp;</span> <span style="background-color:<?/*=$po_bg_color*/?>">&nbsp;PI & PO Overlap&nbsp;</span><br /></b></font>-->
        <br />

        <table <?=@$attr?> class="tableheader">
            <tr>
                <td align="right"><font style="font-size:<?=$font_size?>px">日&nbsp;<br />月&nbsp;</font></td>
                <?
                //顶部表头**
                foreach($chart_date as $v){
                    if($v == date('Y-m-d')){
                        echo '<td align="center" width="'.$td_width.'px" style="font-size:'.$font_size.'px; background-color:#FF0000;"><b><font color="#FFFFFF">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</font></b></td>';
                    }else{
                        echo '<td align="center" width="'.$td_width.'" style="font-size:'.$font_size.'px">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</td>';
                    }
                }
                //*********
                ?>
            </tr>
            <?

            foreach($po as $v){
                echo '<tr align="left" height="'.$tr_height.'px">';

                //左侧表头td***
                echo '<td style="font-size:'.$font_size.'px">';
                if(isset($v['pcid'])){
                    echo "<a href='?act=viewpurchase&viewid=".$v['pcid']."'>".$v['pcid']."</a>&nbsp;";
                }
                echo '</td>';
                //************

                foreach($chart_date as $w){
                    //20141222 加status显示
                    $status = '';
                    if(isset($v['d_date']) && $v['d_date'] != '' && $w == $v['d_date']){
                        $status = '<font style="color: '.get_status_color($v['istatus']).'">'.$v['istatus'].'</font>';
                    }
                    if((isset($v['in_date']) && $v['in_date'] <= $w) && (isset($v['expected_date']) && $v['expected_date'] >= $w)){
                        //fb('@');
                        echo '<td align="center" style="background-color:'.$po_bg_color.'">'.$status.'</td>';
                    }else{
                        //fb('#');
                        echo '<td align="center">'.$status.'</td>';
                    }
                }
                echo '</tr>';
            }

            ?>
        </table>
    </fieldset>
</div>