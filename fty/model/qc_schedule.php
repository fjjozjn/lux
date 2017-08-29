<?php

/*
change log
20130814

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'fty/in38/recordset.class3.php');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
    'qcs_date' => array('title' => '日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['psc_date'])?date('Y-m-d', strtotime($mod_result['psc_date'])):''),
    'pcid' => array('title' => '订单号', 'type' => 'select', 'options' => get_fty_purchase(), 'required' => 1, 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:''),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
    $qcs_date = $_POST['qcs_date'].' '.date('H:i:s');
    $pcid = $_POST['pcid'];

    //20130819 kevin 要求同一个po号能添加多条记录
    //$rtn = $mysql->qone('select id from qc_schedule where pcid = ?', $pcid);
/*    if( isset($rtn['id']) && $rtn['id'] != '' ){
        $result = $mysql->q('update qc_schedule set qcs_date = ?, pcid = ?, mod_date = ?, mod_by = ? where id = ?', $qcs_date, $pcid, dateMore(), $_SESSION['ftylogininfo']['aName'], $rtn['id']);
        if($result){
            $myerror->ok('Modify QC Schedule success!', 'com-qc_schedule&page=1');
        }else{
            $myerror->error('Modify QC Schedule failure!', 'com-qc_schedule&page=1');
        }
    }else{*/
        $result = $mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $qcs_date, $pcid, dateMore(), '', $_SESSION['ftylogininfo']['aName'], '');
        if($result){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_ADD_QC_SCHEDULE, $_SESSION["ftylogininfo"]["aName"]." <i>add qc schedule</i> (purchase '".$pcid."') in sys", ACTION_LOG_SYS_ADD_QC_SCHEDULE_S, "", "", 0);

            $myerror->ok('Add QC Schedule success!', 'com-qc_schedule&page=1');
        }else{
            $myerror->error('Add QC Schedule failure!', 'com-qc_schedule&page=1');
        }
    //}
}

if($myerror->getError()){
    require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
    require_once(ROOT_DIR.'model/inside_ok.php');
}else{
    if($myerror->getWarn()){
        require_once(ROOT_DIR.'model/inside_warn.php');
    }
    ?>
<!--    <h1 class="green">QC Schedule<em>* item must be filled in</em></h1>-->
    <?

    //*******************参数设置*******************
    //设置显示的表格的时间范围 现在是3星期
    $chart_date = cal_date_new(date('Y-m-d',strtotime('-5 day')), date('Y-m-d',strtotime('+15 day')));

    //设置日期和pi po的字体大小
    $font_size = 10;
    //设置日期表头每个单元格宽度
    $td_width = 30;
    //设置除顶部表头外每行的高度
    $tr_height = 20;
    //设置表格只有横线的border
    $attr = 'style=" border-collapse:collapse; border:1px solid #DADADA" rules="rows"';
    //*******************参数设置*******************

    ?>
    <br />
    <br />
    <div align="center" style="width:100%">

        <!--<font color="#0066CC"><b><span style="background-color:<?/*=$pi_bg_color*/?>">&nbsp;PI Colour&nbsp;</span> <span style="background-color:<?/*=$po_bg_color*/?>">&nbsp;PO Colour&nbsp;</span> <span style="background-color:<?/*=$po_bg_color*/?>">&nbsp;PI & PO Overlap&nbsp;</span><br /></b></font>-->
        <br />
        <table <?=$attr?> class="tableheader">
            <tr>
                <td align="right"><font style="font-size:<?=$font_size?>px">day&nbsp;<br />month&nbsp;<br />week&nbsp;</font></td>
                <?
                //顶部表头**
                foreach($chart_date as $v){
                    if($v == date('Y-m-d')){
                        echo '<td align="center" width="'.$td_width.'px" style="font-size:'.$font_size.'px; background-color:#FF0000;"><b><font color="#FFFFFF">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'<br />'.get_week(date('w', strtotime($v))).'</font></b></td>';
                    }else{
                        echo '<td align="center" width="'.$td_width.'" style="font-size:'.$font_size.'px">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'<br />'.get_week(date('w', strtotime($v))).'</td>';
                    }
                }
                //*********
                ?>
            </tr>
            <?
            //20141207 只能看到直接工厂的
            if(isFtyAdmin()){
                $rs = $mysql->q('select q.id, q.pcid, q.qcs_date, p.created_by, s.name from qc_schedule q, purchase p, supplier s where q.pcid = p.pcid and p.sid = s.sid and qcs_date >= ? and qcs_date <= ?', $chart_date[0], $chart_date[count($chart_date)-1]);
            }else{
                $rs = $mysql->q('select q.id, q.pcid, q.qcs_date, p.created_by, s.name from qc_schedule q, purchase p, supplier s where q.pcid = p.pcid and p.sid = s.sid and qcs_date >= ? and qcs_date <= ? and p.sid = ?', $chart_date[0], $chart_date[count($chart_date)-1], $_SESSION['ftylogininfo']['aFtyName']);
            }
            if($rs){
                $rtn = $mysql->fetch();
                echo '<tr align="left" height="'.$tr_height.'px"><td></td>';
                foreach($chart_date as $d){
                    echo '<td align="center" valign="top">';
                    foreach($rtn as $v){
                        if($d == date('Y-m-d', strtotime($v['qcs_date']))){

                            //20151018 检查是否添加了QC_REPORT，如果添加，显示其ID
                            $qc_report_rtn = $mysql->qone('select qc_id from qc_report where pcid = ?', $v['pcid']);
                            if(isset($qc_report_rtn['qc_id']) && $qc_report_rtn['qc_id'] != ''){
                                $qc_report_str = "<a href='?act=modify_qc_report&modid=".$qc_report_rtn['qc_id']."'>".$qc_report_rtn['qc_id']."</a><br />";
                            }else{
                                $qc_report_str = "<a href='?act=add_qc_report&fty_id=".$v['pcid']."'>Add QC Report</a><br />";
                            }

                            //echo "<div style='border:1px solid #CCC;'>".get_fty_color($v['pcid'])."<br />".$v['name']."<br />（".$v['created_by']."）<br /><a href='javascript:void(0)' onclick='del_qc_schedule_item_img(this)' id='".$v['id']."'><img src='/images/button_cancel.jpg' /></a><br /><a href='?act=com-add_qc_report&fty_id=".$v['pcid']."'>Add QC Report</a><br /></div>";
                            echo "<div style='border:1px solid #CCC;'>".get_fty_color($v['pcid'])."<br />".$v['name']."<br />（".$v['created_by']."）<br /><a href='javascript:void(0)' onclick='del_qc_schedule_item_img(this)' id='".$v['id']."'><img src='/images/button_cancel.jpg' /></a><br />".$qc_report_str."</div>";
                        }
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            ?>
        </table>
    </div>

    <br />
    <br />
    <fieldset class="center2col" style="width:45%">
        <legend class='legend'>Setting</legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('qcs_date');?></td>
                <td width="25%"><? $goodsForm->show('pcid');?></td>
                <td width="25%"></td>
                <td width="25%"></td>
            </tr>
        </table>
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?><!--<div style="padding-top: 9px;">&nbsp;&nbsp;<a class="button" href="javascript:void(0)" onclick="del_qc_schedule_item()"><b>Delete</b></a></div>-->

    </fieldset>
    <br />
    <?
    $goodsForm->end();
}
?>

<script>
    //for delete button 屏蔽不用了，还要填东西太麻烦了
    function del_qc_schedule_item(){
        var qcs_date_obj = $('#qcs_date');
        var pcid_obj = $('#pcid');

        if(qcs_date_obj.val() == ''){
            alert('Please fill in the DATE !');
        }else if(pcid_obj.val() == ''){
            alert('Please select PO# !');
        }else{
            var qs = 'ajax=1&act=ajax-del_qc_schedule_item&date='+qcs_date_obj.val()+'&value='+pcid_obj.val();
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('System error, delete qc schedule item failure !');
                },
                success: function(data){
                    if(data.indexOf('!no-') < 0){
                        alert('Delete success !');
                        location.reload();
                    }else{
                        alert('Delete failure !');
                    }
                }
            });
        }
    }

    //for small delete img
    function del_qc_schedule_item_img(obj){
        if(confirm("Are you sure you want to delete?")){
            var id_obj = $(obj);
            var div_obj = id_obj.parent();
            var qs = 'ajax=1&act=ajax-del_qc_schedule_item&value='+id_obj.attr('id');
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('System error, delete qc schedule item failure !');
                },
                success: function(data){
                    if(data.indexOf('!no-') < 0){
                        alert('Delete success !');
                        div_obj.remove();
                    }else{
                        alert('Delete failure !');
                    }
                }
            });
        }
    }
</script>