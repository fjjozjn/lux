<!--div style="text-align:center; margin:500px auto; color:#06C; font-weight:bold;"-->

<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

if(isset($_POST['content']) && $_POST['content'] != ''){
    $mysql->q('insert into bulletin_board (b_from, content, b_date) values (?, ?, ?)',
        $_SESSION['logininfo']['aName'], $_POST['content'], dateMore());
}

$bulletin_board = $mysql->q('select * from bulletin_board where b_from <> ? order by b_date desc limit 30', 'system');
$b_info = '';
if($bulletin_board){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $b_info .= ('('.$v['b_date'].') '.$v['b_from'].' : '.$v['content']."\r\n");
    }
}

?>
<!-- BEGIN CBOX - www.cbox.ws - v001 -->
<!--<div id="cboxdiv" style="text-align: center; line-height: 0">
    <div><iframe frameborder="0" width="96%" height="100" src="http://www4.cbox.ws/box/?boxid=4178127&amp;boxtag=456mhw&amp;sec=main" marginheight="2" marginwidth="2" scrolling="auto" allowtransparency="yes" name="cboxmain4-4178127" style="border:#ababab 1px solid;" id="cboxmain4-4178127"></iframe></div>
    <div><iframe frameborder="0" width="96%" height="75" src="http://www4.cbox.ws/box/?boxid=4178127&amp;boxtag=456mhw&amp;sec=form" marginheight="2" marginwidth="2" scrolling="no" allowtransparency="yes" name="cboxform4-4178127" style="border:#ababab 1px solid;border-top:0px" id="cboxform4-4178127"></iframe></div>
</div>-->
<!-- END CBOX -->

<div align="center" style=" float:left;width:100%;">
    <fieldset class="center2col" style="width:95%; padding-left:5px">
        <legend class='legend'>Bulletin Board</legend>
        <div><textarea readonly="readonly" id="bb_t" rows="5" style="width: 95%;font-size: 12px;"><?php
                echo $b_info; ?></textarea></div>
        <form method="post" action="">
            <div><input type="text" id="content" name="content" style="width: 89%" required="1" strlen="1,500"><input type="submit" value="Submit" style="cursor:pointer;"></div>
        </form>
    </fieldset>
</div>

<?




//从导航栏点过带的会带 today 参数，这样默认显示当天的前5后24天，加当天共30天
$chart_date = cal_date_new(date('Y-m-d',strtotime('-5 day')), date('Y-m-d',strtotime('+24 day')));

//找出在区间内的pi和po
$start_date = $chart_date[0];
$end_date = $chart_date[count($chart_date) -1];
$pipo = array();
if(isSysAdmin()){
    $rs = $mysql->q('select pvid, mark_date, expected_date from proforma where mark_date < ? and expected_date > ? order by expected_date', $end_date, $start_date);
}else{
    $rs = $mysql->q('select pvid, mark_date, expected_date from proforma where mark_date < ? and expected_date > ? AND printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?) order by expected_date', $end_date, $start_date, '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
}
if($rs){
    $rtn = $mysql->fetch();
    $index = 0;
    foreach($rtn as $v){
        $pipo[$index]['pi'][] = $v['pvid'];
        $pipo[$index]['pi'][] = date('Y-m-d', strtotime($v['mark_date']));
        $pipo[$index]['pi'][] = date('Y-m-d', strtotime($v['expected_date']));
        //20130504
        //由PI查询VI的shipment
        $shipment_date = $mysql->qone('select s_date from shipment where pi_no = ? and s_status = ?', $v['pvid'], 'Complete');
        if($shipment_date){
            $pipo[$index]['pi']['s_date'] = date('Y-m-d', strtotime($shipment_date['s_date']));
        }
        //由PI查询VI的payment
        $payment_date = $mysql->qone('select p_date from payment where pi_no = ? and p_status = ?', $v['pvid'], 'Balance');
        if($payment_date){
            $pipo[$index]['pi']['p_date'] = date('Y-m-d', strtotime($payment_date['p_date']));
        }

        //我看了purchase表 每个pi会对应多个po，只不过不是在po号后加A、B、C，而是新的po号
        $po_rs = $mysql->q('select pcid, mark_date, expected_date from purchase where reference = ?', $v['pvid']);
        if($po_rs){
            $po_rtn = $mysql->fetch();
            $index_po = 0;
            foreach($po_rtn as $w){
                $pipo[$index]['po'][$index_po][] = $w['pcid'];
                $pipo[$index]['po'][$index_po][] = date('Y-m-d', strtotime($w['mark_date']));
                $pipo[$index]['po'][$index_po][] = date('Y-m-d', strtotime($w['expected_date']));

                //20140223 在现在的数据中同一个po有开多个qc schedule，日期不同
                $qc_schedule = $mysql->q('select qcs_date from qc_schedule where pcid = ?', $w['pcid']);
                if($qc_schedule){
                    $qc_rtn = $mysql->fetch();
                    foreach($qc_rtn as $x){
                        $pipo[$index]['pi']['qc_date'][] = date('Y-m-d', strtotime($x['qcs_date']));
                    }
                }

                $index_po++;
            }
        }
        $index++;
    }
}

//fb($pipo);

//*******************参数设置*******************
//设置日期和pi po的字体大小
$font_size = '10px';
//设置日期表头每个单元格宽度
$td_width = '14px';
//左侧表头宽度
$left_td_width = '100px';
//设置除顶部表头外每行的高度
$tr_height = '20px';
//设置pi进度条背景颜色
$pi_bg_color = '#CCE8CF';
//设置po进度条背景颜色
$po_bg_color = '#66CCFF';
//设置pi与po重复了的进度条背景颜色
$pipo_bg_color = '#40AA53';
//状态 S 的颜色
$s_color = '#FF7D00';
//状态 P 的颜色
$p_color = '#FFFF00';
//状态 QC 的颜色
$qc_color = '#FF0000';
//当天的边框的颜色
$today_color = '#FF6666';
//表格横线颜色
$heng_color = '#DADADA';
//当天的格子，只有竖线
//$table_td_attr = 'border-style: none solid none solid; border-color: '.$today_color.'; border-width: 1px;';
$table_td_attr = 'border-style:solid; border-color: '.$heng_color.' '.$today_color.' '.$heng_color.' '.$today_color.'; border-width: 1px;';
//设置表格只有横线的border
$table_attr = 'style=" border:1px solid '.$heng_color.'" rules="rows"';
//*******************参数设置*******************

?>
<div align="center" style=" float:left;width:50%;">
    <fieldset class="center2col" style="width:90%; padding-left:5px">
        <legend class='legend'>Gantt Chart</legend>
        <font color="#0066CC"><b><span style="background-color:<?=$pi_bg_color?>">&nbsp;PI Colour&nbsp;</span> <span style="background-color:<?=$po_bg_color?>">&nbsp;PO Colour&nbsp;</span> <span style="background-color:<?=$pipo_bg_color?>">&nbsp;PI & PO Overlap&nbsp;</span><br /></b></font>
        <br />
        <div style="padding-right: 16px;">
        <table <?=@$table_attr?>>
            <tr>
                <td align="right" style="width:<?=$left_td_width?>;"><font style="font-size:<?=$font_size?>;">Day&nbsp;<br />Month&nbsp;</font></td>
                <?
                //顶部表头**
                foreach($chart_date as $v){
                    if($v == date('Y-m-d')){
                        echo '<td align="center" style="width:'.$td_width.'; font-size:'.$font_size.'; background-color:'.$today_color.';"><b><font color="#FFFFFF">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</font></b></td>';
                    }else{
                        echo '<td align="center" style="width:'.$td_width.'; font-size:'.$font_size.'">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</td>';
                    }
                }
                //*********
                ?>
            </tr>
        </table>
        </div>
        <div style="height: 299px; overflow-y: auto;">
            <table <?=@$table_attr?>>
                <?
                foreach($pipo as $v){
                    echo '<tr align="left" height="'.$tr_height.'">';

                    //左侧表头td***
                    echo '<td style="font-size:'.$font_size.'; width:'.$left_td_width.';">';
                    echo "<a href='?act=com-modifyproforma&modid=".$v['pi'][0]."'>".$v['pi'][0]."</a>&nbsp;";
                    if(isset($v['po'])){
                        $title = '';
                        foreach($v['po'] as $u){
                            $title .= $u[0].' ';
                            //echo "<a href='?act=com-modifypurchase&modid=".$u[0]."'>".$u[0]."</a>&nbsp;";
                        }
                        echo "<a href='javascript:void(0);' title='".$title."'>PO...</a>&nbsp;";
                    }
                    echo '</td>';
                    //************

                    foreach($chart_date as $w){
                        //标志：确定这个格用什么颜色的背景。0无色，1 pi色（最浅），2 po色（略深），3 pi和po重复的色（最深），4 当天显示为红色边框
                        //同时符合pi 和 po ，则显示po 的颜色
                        $sign = 0;

                        //20140117 当天在原来色的基础上加显示为红色左右边框
                        $today_border = '';
                        if($w == date('Y-m-d')){
                            $today_border = $table_td_attr;
                        }

                        //pi**
                        if($v['pi'][1] <= $w && $v['pi'][2] >= $w){
                            $sign = 1;
                        }
                        //****

                        //po**
                        if(isset($v['po'])){
                            foreach($v['po'] as $u){
                                if($u[1] <= $w && $u[2] >= $w){
                                    if($sign == 1){
                                        $sign = 3;
                                    }elseif($sign == 0){
                                        $sign = 2;
                                    }
                                }
                            }
                        }
                        //****


                        //20130504 加s与p的状态
                        $status = '';
                        if(isset($v['pi']['s_date']) && $v['pi']['s_date'] == $w){
                            $status .= '<span style="color: '.$s_color.';"><b>S</b></span><br />';
                        }
                        if(isset($v['pi']['p_date']) && $v['pi']['p_date'] == $w){
                            $status .= '<span style="color: '.$p_color.';"><b>P</b></span><br />';
                        }
                        if(isset($v['pi']['qc_date'])){
                            foreach($v['pi']['qc_date'] as $z){
                                if($z == $w)
                                    $status .= '<span style="color: '.$qc_color.';"><b>Q</b></span><br />';
                            }
                        }

                        if($sign == 1){
                            echo '<td align="center" style="'.$today_border.' background-color:'.$pi_bg_color.'; width:'.$td_width.';">'.$status.'</td>';
                        }elseif($sign == 2){
                            echo '<td align="center" style="'.$today_border.' background-color:'.$po_bg_color.'; width:'.$td_width.';">'.$status.'</td>';
                        }elseif($sign == 3){
                            echo '<td align="center" style="'.$today_border.' background-color:'.$pipo_bg_color.'; width:'.$td_width.';">'.$status.'</td>';
                        }else{
                            echo '<td align="center" style="'.$today_border.' width:'.$td_width.'">'.$status.'</td>';
                        }
                    }
                    echo '</tr>';

                    //每个pi和相应的po块结束后，下面有个空行
                    //echo '<tr><td>&nbsp;</td></tr>';
                }

                ?>
            </table>
        </div>
    </fieldset>
</div>
<style>
        /*20130427*/
    .td_chart_highlight {
        background-color:#F3F3F3;
    }
</style>

<!--script>
jQuery.fn.fixedtableheader = function(options) { var settings = jQuery.extend({ headerrowsize: 1, highlightrow: false, highlightclass: "highlight" }, options); this.each(function(i) { var $tbl = $(this); var $tblhfixed = $tbl.find("tr:lt(" + settings.headerrowsize + ")"); var headerelement = "th"; if ($tblhfixed.find(headerelement).length == 0) headerelement = "td"; if ($tblhfixed.find(headerelement).length > 0) { $tblhfixed.find(headerelement).each(function() { $(this).css("width", $(this).width()); }); var $clonedTable = $tbl.clone().empty(); var tblwidth = GetTblWidth($tbl); $clonedTable.attr("id", "fixedtableheader" + i).css({ "position": "fixed", "top": "0", "left": $tbl.offset().left }).append($tblhfixed.clone()).width(tblwidth).hide().appendTo($("body")); if (settings.highlightrow) $("tr:gt(" + (settings.headerrowsize - 1) + ")", $tbl).hover(function() { $(this).addClass(settings.highlightclass); }, function() { $(this).removeClass(settings.highlightclass); }); $(window).scroll(function() { if (jQuery.browser.msie && jQuery.browser.version == "6.0") $clonedTable.css({ "position": "absolute", "top": $(window).scrollTop(), "left": $tbl.offset().left }); else $clonedTable.css({ "position": "fixed", "top": "0", "left": $tbl.offset().left - $(window).scrollLeft() }); var sctop = $(window).scrollTop(); var elmtop = $tblhfixed.offset().top; if (sctop > elmtop && sctop <= (elmtop + $tbl.height() - $tblhfixed.height())) $clonedTable.show(); else $clonedTable.hide(); }); $(window).resize(function() { if ($clonedTable.outerWidth() != $tbl.outerWidth()) { $tblhfixed.find(headerelement).each(function(index) { var w = $(this).width(); $(this).css("width", w); $clonedTable.find(headerelement).eq(index).css("width", w); }); $clonedTable.width($tbl.outerWidth()); } $clonedTable.css("left", $tbl.offset().left); }); } }); function GetTblWidth($tbl) { var tblwidth = $tbl.outerWidth(); return tblwidth; } };

$(function () {
	$(".tableheader").fixedtableheader({ highlightrow: true, highlightclass: "td_chart_highlight", headerrowsize: 1 });
});
</script-->
<?



/*//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		'content' => array('title' => 'Content', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'required' => 1),			
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
		);	
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	$b_date = dateMore();
	$b_from = $_SESSION['logininfo']['aName'];
	$content = $_POST['content'];
	$result = $mysql->q('insert into bulletin_board (b_from, b_date, content) values ('.moreQm(3).')', $b_from, $b_date, $content);
	if($result){
		$myerror->ok('Success!', 'main&page=1');
	}else{
		$myerror->error('Failure!', 'main&page=1');
	}	
}

if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}
//不显示提示成功的信息，这样更方便点
/*elseif($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}*/


/*else{

	if($myerror->getWarn()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}*/


?>
<div style="width:50%; float:left;">
    <fieldset class="center2col" style="width:90%; padding-left:5px">
        <legend class='legend'>Delay</legend>
        <div align="right"><a class="button" href="/task/delay_refresh.php">REFRESH</a><a class="button" href="?act=bulletin_board&page=1">More</a></div>
        <div style="height: 340px; overflow-y: auto;">
            <table>
                <?
                //mod 20121127 非system的信息置顶显示，且显示5条
                //mod 20121211 改为全部按时间先后显示
                /*
                $rs1 = $mysql->q('select * from bulletin_board where b_from <> ? order by b_date desc limit 5', 'system');
                if($rs1){
                    $rtn = $mysql->fetch();
                    foreach($rtn as $v){
                        echo '<tr><td valign="top" width="130">[ '.$v['b_from'].' '.date('d/m', strtotime($v['b_date'])).' ]</td><td>'.str_replace("\r\n", '<br />', match_id($v['content'])).'</td>';
                    }
                }
                */
                //system的信息只显示 b_date 是当天的记录，即只显示需要提醒的，因为，已经完成的不需要提醒的记录的时间就不再更新了，也就不会是当天的了
                //mod 20121211
                //$rs2 = $mysql->q('select * from bulletin_board where TO_DAYS(b_date) = TO_DAYS(NOW()) and b_from = ? limit 100', 'system');
                //20130627 去掉了limit 50的限制，因为现在用户在这里的发消息的功能去掉了，只显示系统提示的pi过期信息和overdue的shipment
                //20140426 只显示 b_from 为 system
                if(isSysAdmin()){
                    $rs2 = $mysql->q('select * from bulletin_board where TO_DAYS(b_date) = TO_DAYS(NOW()) AND b_from = ? order by content desc', 'system');
                }else{
                    $rs2 = $mysql->q('select * from bulletin_board where TO_DAYS(b_date) = TO_DAYS(NOW()) AND b_from = ? AND b_to in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?) order by content desc', 'system', '%'.$_SESSION['logininfo']['aName'].'%', '%'.$_SESSION['logininfo']['aName'].'%');
                }
                if($rs2){
                    $rtn = $mysql->fetch();
                    foreach($rtn as $v){
                        echo '<tr><td valign="top" width="130">[ '.$v['b_from'].' '.date('d/m', strtotime($v['b_date'])).' ]</td><td>'.$v['b_to'].' </td><td>'.str_replace("\r\n", '<br />', match_id($v['content'])).'</td>';
                    }
                }
                ?>
            </table>
        </div>
    </fieldset>
    <!--<br />
<br />
<fieldset class="center2col" style="width:90%; padding-left:5px">
<legend class='legend'><?/*=isset($_GET['modid'])?'Modify':'Add' */?></legend>

<?php
/*$goodsForm->begin();
*/?>
<table width="100%" id="table">
  <tr>
  	<td width="50%"><?/* $goodsForm->show('content');*/?></td>
  </tr>
</table>
<div class="line"></div>
<?/*
$goodsForm->show('submitbtn');
*/?>
</fieldset>
<br />
--><?/*
$goodsForm->end();


}

*/?>

</div>

<div style="width: 100%; float: right;">
    <fieldset class="center2col" style="width:95%; padding-left:5px">
        <legend class='legend'>USER LOG</legend>
        <div style="height: 100px; overflow-y: auto;">
            <table>
                <?php
                if(isSysAdmin()){
                    $rs3 = $mysql->q("select AdminHistDate, AdminHistRemark from tw_admin_hist where AdminHistCatg like ? and AdminID <> 4 order by AdminHistDate desc limit 50", 'action_log_%');
                }else{
                    $rs3 = $mysql->q("select AdminHistDate, AdminHistRemark from tw_admin_hist where AdminHistCatg like ? and AdminID <> 4 and AdminID in (select AdminID from tw_admin where AdminLuxGroup LIKE ? OR AdminName = ?) order by AdminHistDate desc limit 50", 'action_log_%', '%'.$_SESSION['logininfo']['aName'].'%', '%'.$_SESSION['logininfo']['aName'].'%');
                }

                if($rs3){
                    $rtn = $mysql->fetch();
                    foreach($rtn as $v){
                        echo '<tr><td>'.$v['AdminHistDate'].' : '.$v['AdminHistRemark'].'</td></tr>';
                    }
                }
                ?>
            </table>
        </div>
    </fieldset>
</div>